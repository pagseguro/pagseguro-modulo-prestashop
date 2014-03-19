<?php

include_once dirname(__FILE__) . '/../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) . '/../features/validation/pagsegurovalidateorderprestashop.php';

$conciliacao = new PagSeguroConciliacao();

if(isset($_POST['idOrder'])) {
    $conciliacao->createLog($_POST);
    die(Util::createAddOrderHistory($_POST['idOrder'],$_POST['newIdStatus']));
} else if(isset($_POST['dias'])) {
    echo json_encode($conciliacao->getTableResult());
} else {
    return $conciliacao->getTableResult();
}

class PagSeguroConciliacao {

    private $obj_credential = "";

    private $errorMsg = false;

    private $tableResult = "";

    private $idStatusPagseguro;

    public function getTableResult() {
        $this->setObjCredential();
        $sql = $this->getTableResults();
        $tableResult = $this->getTable($sql);

        return $tableResult;
    }

    private function getTable($sql) {
        if ($results = Db::getInstance()->executeS($sql)) {
            if(!empty($this->obj_credential)){
                $idAbandoned = $this->getAbandoned();
            }
            $status = $this->getPaidStatus();
            foreach ($results as $key => $row) {
                $row['status_pagseguro'] = '';
                $row['id_status_pagseguro'] = '';
                $row['id_pagseguro'] = '';
                $imagesResults = '';

                foreach ($status as $rowStatus) {
                    if($row['id_order'] == $rowStatus['id_order']) {
                        if(!empty($this->obj_credential)){
                            try {
                                $idStatus = PagSeguroTransactionSearchService::searchByCode(
                                        $this->obj_credential,
                                        $rowStatus['id_transaction']
                                );
                                $row['id_pagseguro'] = $idStatus->getReference();
                                $row['id_status_pagseguro'] = $idStatus->getStatus()->getvalue();
                                $row['status_pagseguro'] = Util::getStatusCMS($row['id_status_pagseguro']);
                            } catch (PagSeguroServiceException $e) {
                            } catch (Exception $e) { }
                        }
                        break;
                    } else {
                        if(isset($idAbandoned)){
                            foreach ($idAbandoned as $abandoned) {
                                if ($row['id_order'] == $abandoned) {
                                    $row['status_pagseguro'] = 'Abandonada';
                                    $row['id_pagseguro'] = $abandoned;
                                    break;
                                }
                            }
                        }
                    }
                }

                $statusPagSeguro = $this->getPagSeguroState($row['status_pagseguro']);
                $imagesResults = $this->getImages($statusPagSeguro,$row);

                $this->tableResult .=  "
                    <tr class='tabela' id='" .$row['id_order']."'
                        style='color:".$this->getColor($row['id_order_state'],$row['status_pagseguro'])."'>
                    <td>" .$row['date_add']."</td>
                    <td>" .$row['id_order']."</td>
                    <td>" .$row['id_pagseguro']."</td>
                    <td>" .$row['name']."</td>
                    <td>". $row['status_pagseguro']."</td>
                    <td id='editar'>
                        <a onclick='editRedirect(" . $row['id_order'] . ")'
                            id='" . $row['id_order'] . "' style='cursor:pointer'>
                        <img src='../modules/pagseguro/assets/images/edit.png'
                            border='0' alt='edit' title='Editar'/>
                        </a>
                    </td>
                    <td id='duplicar'>
                        " . $imagesResults . "
                    </td>
                    </tr>
                ";
            }
        }

        return array('tabela' => $this->tableResult,'errorMsg' => $this->errorMsg);
    }

    private function getImages($statusPagSeguro,$row) {
        $retorno = "<img src='../modules/pagseguro/assets/images/refreshDisabled.png'
                        border='0' alt='edit' title='Modificar'/>
                    ";

        if(empty($statusPagSeguro)){
            return $retorno;
        }

        foreach ($statusPagSeguro as $status) {
            if($status['id_order_state'] == $row['id_order_state']) {
                return $retorno;
            }
        }

        $newStatus = empty($statusPagSeguro) ? "" : $statusPagSeguro[count($statusPagSeguro)-1]['id_order_state'];
        $status = $row['status_pagseguro'];

        return "<a onclick='duplicateStatus(
                    " . $row['id_order'] . ",
                    " . $newStatus . ",
                    " . $row['id_order_state'] . ",
                    \" $status \"
                )' style='cursor:pointer'>
                <img src='../modules/pagseguro/assets/images/refresh.png'
                    border='0' alt='edit' title='Modificar'/>
                ";
    }

    private function getPagSeguroState($pagSeguroState,$where = '') {
        $sql = 'SELECT distinct os.`id_order_state`
                        FROM `' . _DB_PREFIX_ . 'order_state` os
                        INNER JOIN `' . _DB_PREFIX_ .'order_state_lang` osl ON
                            (os.`id_order_state` = osl.`id_order_state`
                            AND osl.`name` = \''. $pagSeguroState . '\')'
                                            . $where ;

        $id_order_state = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $id_order_state;
    }

    private function where($state) {
        $where = "os.id_order_state = " . $state;
        if(version_compare(_PS_VERSION_, '1.5.0.3', '>')) {
            return " WHERE deleted = 0 AND ". $where;
        } else {
            return " WHERE " . $where;
        }
    }

    private function getColor($state,$pagSeguroState) {
        $where = $this->where($state);
        $id_order_state = $this->getPagSeguroState($pagSeguroState,$where);

        if($id_order_state == 0) {
            return 'red';
        }
        foreach ($id_order_state as $id_state) {
            if($state == $id_state['id_order_state']) {
                return 'green';
            }
        }

        return 'red';
    }

    private function getAbandoned() {
        $dataInicial = mktime(0, 0, 0, date("m"), date("d")-(isset($_POST['dias']) ? $_POST['dias'] : '5'), date("Y"));
        $dataFinal = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $idAbandoned = array();

        try {
            $obj_transaction = PagSeguroTransactionSearchService::searchAbandoned(
                    $this->obj_credential,1,1000,$dataInicial,$dataFinal
            );
            if($obj_transaction->getTotalPages() >= 1) {
                for ($i = 1; $i <= $obj_transaction->getTotalPages(); $i++) {
                    if ($i > 1) {
                        $obj_transaction = PagSeguroTransactionSearchService::searchAbandoned(
                                $this->obj_credential,$i,1,$dataInicial,$dataFinal
                        );
                    }
                    foreach ($obj_transaction->getTransactions() as $row) {
                        $idAbandoned[] = $row->getReference();
                    }
                }
            }
            
        } catch (PagSeguroServiceException $e) {
        } catch (Exception $e) { }

        return $idAbandoned;
    }

    private function setObjCredential() {
        $email = Configuration::get('PAGSEGURO_EMAIL');
        $token = Configuration::get('PAGSEGURO_TOKEN');
        if(!empty($email) && !empty($token)) {
            $this->obj_credential = new PagSeguroAccountCredentials($email, $token);
        } else {
            $this->errorMsg = true;
        }
    }

    private function getPaidStatus() {
        $status = array();
        $sqlStatus = "SELECT * FROM "._DB_PREFIX_."pagseguro_order";
        if ($results = Db::getInstance()->executeS($sqlStatus)) {
            foreach ($results as $key => $row) {
                $status[] = array('id_order' => $row['id_order'],
                        'id_transaction' => $row['id_transaction']);
            }
        }

        return $status;
    }

    private function getTableResults() {
        $sql = 'SELECT
                    psord.id_order,
                    psord.date_add,
                    osl.`name`,
                    oh.id_order_state,
                    (SELECT COUNT(od.`id_order`) FROM `ps_order_detail` od
                            WHERE od.`id_order` = psord.`id_order`
                            GROUP BY `id_order`) AS product_number
                FROM `'._DB_PREFIX_.'orders` AS psord
                        LEFT JOIN `'._DB_PREFIX_.'order_history` oh
                            ON (oh.`id_order` = psord.`id_order`)
                        LEFT JOIN `'._DB_PREFIX_.'order_state` os
                            ON (os.`id_order_state` = oh.`id_order_state`)
                        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
                            ON (os.`id_order_state` = osl.`id_order_state`)
                WHERE oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh
                                                    WHERE moh.`id_order` = psord.`id_order`
                                                    GROUP BY moh.`id_order`)
                    AND psord.payment = "PagSeguro"
                    AND osl.`id_lang` = psord.id_lang
                    AND psord.date_add >= DATE_SUB(CURDATE(),INTERVAL \''
                        .(isset($_POST['dias']) ? $_POST['dias'] : '5').
                    '\' DAY)';
        return $sql;
    }

    public function createLog($dados) {
        PagSeguroConfig::activeLog();
        LogPagSeguro::info("PagSeguroConciliation.Register( 'Alteração de Status da compra '" . 
            $dados['idOrder'] . "' para o Status '" . $dados['newStatus'] . "(" . 
            $dados['newIdStatus'] . ")' - '" . date("d/m/Y H:i") . "') - end");
    }
}
