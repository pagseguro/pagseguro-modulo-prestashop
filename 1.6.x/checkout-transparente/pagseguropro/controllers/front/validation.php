<?php
/*
 * 2018 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente 
 *
 */

class PagSeguroProValidationModuleFrontController extends ModuleFrontController 
{
    public $pagseguro_msg;
    public $cod_transacao;
    public $cod_status;
    public $paymentMethodType;
    public $paymentMethodCode;
    public $paymentLink;
    public $endereco_final;
    public $payment_option;
    public $tipo_pagamento;
    public $pedido = false;
    public $autorizacao;
    public $insert_data;
    public $doc;
    public $id_order;
    public $cart_id;

    public function postProcess()
    {
		if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 || $this->context->cart->id_address_invoice == 0 || !$this->module->active) {
			Tools::redirect('index.php?controller=order&step=1');
		}
		$authorized = false;
		foreach (Module::getPaymentModules() as $module) 
		{
			if ($module['name'] == 'pagseguropro') {
				$authorized = true;
				break;
			}
		}
		if (!$authorized) {
			die($this->module->l('This payment method is not available.', 'validation'));
		}

        if (in_array($this->context->currency->iso_code, $this->module->limited_currencies) == false) {
			$this->$pagseguro_msg = $this->module->l('Moeda não permitida para esta opção de pagamento.', 'validation');

			if(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc'){
				$this->endereco_final = 'index.php?controller=order-opc&isPaymentStep=true&pagseguro_msg='.htmlentities(urlencode($pagseguro_msg));
			}else{
				$this->endereco_final = 'index.php?controller=order&step=3&pagseguro_msg='.htmlentities(urlencode($pagseguro_msg));
			}
		}

		$this->tipo_pagamento = Tools::getValue('ps_tipo');

        if ($this->tipo_pagamento == 'cartao') {
			$this->doc = preg_replace('/[^0-9]/','', Tools::getValue('card_doc'));
			$cpf_cnpj = strlen($this->doc) > 12 ? 'cnpj' : 'cpf';
			$birthday = Tools::getValue('card_birth');
			$birthDate = date('d/m/Y',strtotime($birthday));
            $dados_form = array(
                'cart_id' => $this->context->cart->id,
                'titular_cartao' => Tools::getValue('card_name'),
                'numero_cartao' => Tools::getValue('card_number'),
                'mes_venc_cartao' => Tools::getValue('card_month'),
                'ano_venc_cartao' => Tools::getValue('card_year'),
                'cod_seg_cartao' => Tools::getValue('card_cvv'),
                'total_parcelas_cartao' => Tools::getValue('ps_cartao_parcelas'),
                'valor_parcelas_cartao' => Tools::getValue('ps_cartao_valor_parcela'),
                'data_nasc' => $birthDate,
                'telefone' => Tools::getValue('card_phone'),
                'doc' => $cpf_cnpj,
                $cpf_cnpj => $this->doc,
                'endereco_cobranca' => Tools::getValue('ps_cartao_endereco_cobranca'),
                'cep_cobranca' => Tools::getValue('ps_cartao_cep_cobranca'),
                'numero_cobranca' => Tools::getValue('ps_cartao_numero_cobranca'),
                'complemento_cobranca' => Tools::getValue('ps_cartao_complemento_cobranca'),
                'bairro_cobranca' => Tools::getValue('ps_cartao_bairro_cobranca'),
                'cidade_cobranca' => Tools::getValue('ps_cartao_cidade_cobranca'),
                'uf_cobranca' => Tools::getValue('ps_cartao_uf_cobranca'),
                'token' => Tools::getValue('ps_cartao_token'),
                'hash' => Tools::getValue('ps_cartao_hash'),
            );
            $this->autorizacao = $this->module->processarCartao($dados_form);
            if (!$this->autorizacao || (string)$this->autorizacao->code == '' || !(string)$this->autorizacao->code) {
				$this->module->saveLog('validation', 'Processa Cartao', $this->context->cart->id, json_encode($this->module->ps_params), json_encode($this->module->ps_errors));
				if (Configuration::get('PAGSEGUROPRO_MODO') == 0) {
					Tools::p($this->module->ps_errors);
					Tools::p($this->module->ps_params);
				}else{
					//$this->retornoErroApi();
					$this->criaPedidoErroApi();
				}
            }
        }elseif ($this->tipo_pagamento == 'boleto') {
		if(Configuration::get('PAGSEGUROPRO_TIPO_DESCONTO_BOLETO') >= 1 && Configuration::get('PAGSEGUROPRO_VALOR_DESCONTO_BOLETO') >= 1)
		{
			$this->module->geraDesconto($this->context->cart);
		}
			$this->doc = preg_replace('/[^0-9]/','', Tools::getValue('boleto_doc'));
			$cpf_cnpj = strlen($this->doc) > 12 ? 'cnpj' : 'cpf';
			$dados_form = array(
				'cart_id' => $this->context->cart->id,
				'telefone' => Tools::getValue('boleto_phone'),
				'doc' => $cpf_cnpj,
				$cpf_cnpj => $this->doc,
				'hash' => Tools::getValue('ps_boleto_hash'),
			);
            
			$this->autorizacao = $this->module->processarBoleto($dados_form);
            if (!$this->autorizacao || (string)$this->autorizacao->code == '' || !(string)$this->autorizacao->code) {
				$this->module->saveLog('validation', 'Processa Boleto', $this->context->cart->id, json_encode($this->module->ps_params), json_encode($this->module->ps_errors));
				if (Configuration::get('PAGSEGUROPRO_MODO') == 0) {
					Tools::p($this->module->ps_errors);
					Tools::p($this->module->ps_params);
				}else{
					$this->retornoErroApi();
				}
            }
        }else {
			$this->doc = preg_replace('/[^0-9]/','', Tools::getValue('transf_doc'));
			$cpf_cnpj = strlen($this->doc) > 12 ? 'cnpj' : 'cpf';
            $dados_form = array(
                'cart_id' => $this->context->cart->id,
                'banco' => Tools::getValue('ps_transf'),
                'telefone' => Tools::getValue('transf_phone'),
                'doc' => $cpf_cnpj,
                $cpf_cnpj => $this->doc,
                'hash' => Tools::getValue('ps_transf_hash'),
            );
            
			$this->autorizacao = $this->module->processarTransf($dados_form);
            if (!$this->autorizacao || (string)$this->autorizacao->code == '' || !(string)$this->autorizacao->code) {
				$this->module->saveLog('validation', 'Processa Transeferencia', $this->context->cart->id, json_encode($this->module->ps_params), json_encode($this->module->ps_errors));
				if (Configuration::get('PAGSEGUROPRO_MODO') == 0) {
					Tools::p($this->module->ps_errors);
					Tools::p($this->module->ps_params);
				}else{
					$this->retornoErroApi();
				}
            }
        }
		
		//Valida autorização
		if (isset($this->autorizacao) && $this->autorizacao) {
			$this->cod_transacao = (string)$this->autorizacao->code;
			$this->paymentMethodType = (int)$this->autorizacao->paymentMethod->type;
			$this->paymentMethodCode = (int)$this->autorizacao->paymentMethod->code;
			$this->paymentLink = (string)$this->autorizacao->paymentLink;
			$this->cod_status = (int)$this->autorizacao->status;
			$this->referencia = (string)$this->autorizacao->reference;
			/*	1 => 'Aguardando pagamento',
				2 => 'Em análise',
				3 => 'Pagamento confirmado',
				4 => 'Valor disponível',
				5 => 'Em disputa',
				6 => 'Valor pago devolvido ao comprador',
				7 => 'Transação cancelada',
				8 => 'Devolvido',
				9 => 'Retido (chargeback)',
				11 => 'Transação cancelada' (p/ alguns cartões de teste) 
			*/

			if ($this->tipo_pagamento == 'cartao') {
				//Tools::p($this->cod_status);
				if ($this->cod_status == 1) {
					$this->retornoAtualizado($this->autorizacao, 20);
				}elseif ($this->cod_status == 2 || $this->cod_status == 3) {
					$this->criaPedidoInicial($this->autorizacao);
				}elseif ($this->cod_status == 7 || $this->cod_status == 11) {
					$this->retornoNegado();
				}else{
					$this->criaPedidoErroApi();
				}
			}elseif($this->tipo_pagamento == 'transf') {
				$this->criaPedidoInicial($this->autorizacao);
				$this->retornoAtualizado($this->autorizacao, 10);				
			}elseif($this->tipo_pagamento == 'boleto') {
				$this->criaPedidoInicial($this->autorizacao);
				$this->endereco_final = 'index.php?controller=order-confirmation&id_cart='.$this->context->cart->id.'&id_module='.$this->module->id.'&id_order='.$this->id_order.'&key='.$this->context->cart->secure_key;
			    $this->redirectFinal();
			}
		}
    }
    
	/*
	 * Cria pedido com Status inicial "PagSeguro iniciado"
	 */
    private function criaPedidoInicial($transacao)
    {
	    if ($this->pedido !== false){
	        return;
	    }
		$this->cod_status = (int)$transacao->status;
		$this->cart_id = (int)$this->context->cart->id;
        $secure_key = $this->context->customer->secure_key;
		$total_compra = (float)$this->context->cart->getOrderTotal(true, Cart::BOTH);
		$this->payment_option = $this->module->parseTipoPagamento($this->autorizacao->paymentMethod->code);

		if (!$this->module->validateOrder($this->context->cart->id, Configuration::get('_PS_OS_PAGSEGUROPRO_0'), $total_compra, $this->payment_option, NULL, NULL, $this->context->currency->id, false, $secure_key)) {
		    $this->module->saveLog('error', 'Criar Pedido', $this->context->cart->id, json_encode($transacao), 'Erro ao criar pedido inicial.');
		}else{
    		$this->id_order = $this->module->currentOrder;
		    $this->pedido = new Order((int)$this->id_order);
			$this->module->updateOrderStatus($this->context->cart->id, (int)$this->cod_status, (int)$this->id_order);
		}
        if($this->pedido !== false) {
			if ($this->cod_status == 2 || $this->cod_status == 3) {
			    $this->module->updateOrderStatus($this->context->cart->id, $this->cod_status, (int)$this->id_order);
			}
		    $this->endereco_final = 'index.php?controller=order-confirmation&id_cart='.$this->context->cart->id.'&id_module='.$this->module->id.'&id_order='.$this->id_order.'&key='.$this->context->cart->secure_key;
    	    $this->redirectFinal();
        }
    }

	/*
	 * Processa retorno de transação negada sem criar pedido
	 * Redireciona o usuário para o carrinho novamente para retentativa
	 */
    private function retornoNegado()
    {
		$pagseguro_msg = $this->module->l('Pagamento não autorizado. Algum dado pode estar incorreto ou ocorreu algum problema com a operadora. Por favor, revise seus dados de pagamento e tente novamente.');

		$this->context->smarty->assign('pagseguro_msg',$pagseguro_msg);

		if(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc'){
			$this->endereco_final = 'index.php?controller=order-opc&isPaymentStep=true&pagseguro_msg='.htmlentities(urlencode($pagseguro_msg));
		}else{
			$this->endereco_final = 'index.php?controller=order&step=3&pagseguro_msg='.htmlentities(urlencode($pagseguro_msg));
		}
		$this->redirectFinal();
    }
    
	/*
	 * Consulta status atual do pedido e atualiza status 
	 * Caso o pedido de cartão permaneça em "Aguardando Pagamento", cria o pedido em contingência
	 */
    private function retornoAtualizado($transacao, $wait = false)
    {
        if (!$wait){
            $wait = 30;
        }
		sleep((int)$wait);

		$transaction = $this->module->getTransaction($this->cod_transacao);
		if (!$transaction){
			$this->module->saveLog('validation', 'Consulta Transação', $this->context->cart->id, $this->cod_transacao, json_encode($transacao));
		}else{
			$this->cod_status = (int)$transaction->status;
			if ($this->cod_status == 7 || $this->cod_status == 11) {
				$this->retornoNegado();
			}elseif ($this->cod_status == 2 || $this->cod_status == 3){
			    if ($this->pedido === false){
    			    $this->criaPedidoInicial($transaction);
					//sleep(5);
			    }else{
			        $this->module->updateOrderStatus($this->context->cart->id, (int)$this->cod_status, (int)$this->id_order);
			    }
			}elseif($this->cod_status == 1){
			    sleep((int)$wait);
			    $transaction = $this->module->getTransaction($this->cod_transacao);
			    $this->cod_status = (int)$transaction->status;
			    if($this->cod_status == 1){
			        $this->retornoContingencia($transacao);
			    }
			}
		}
    }
    
	/*
	 * Cria o pedido em contingência
	 */
    private function retornoContingencia($transacao)
    {
        $this->criaPedidoInicial($transacao);
        if ($this->id_order === false){
            $id_cart = $this->module->getIdCart($transacao->reference); 
            $this->id_order = Order::getOrderByCartId((int)$id_cart);
        }
    }
    
    private function criaPedidoErroApi()
    {
	    if ($this->pedido !== false){
	        return;
	    }
		$this->cod_status = 1;
		$this->cart_id = (int)$this->context->cart->id;
        $secure_key = $this->context->customer->secure_key;
		$total_compra = (float)$this->context->cart->getOrderTotal(true, Cart::BOTH);
		$this->payment_option = $this->module->parseTipoPagamento($this->autorizacao->paymentMethod->code);

		if (!$this->module->validateOrder($this->context->cart->id, Configuration::get('_PS_OS_PAGSEGUROPRO_0'), $total_compra, $this->payment_option, NULL, NULL, $this->context->currency->id, false, $secure_key)) {
		    $this->module->saveLog('error', 'Criar Pedido', $this->context->cart->id, json_encode($this->autorizacao), 'Erro ao criar pedido inicial após erro da API.');
		}else{
    		$this->id_order = $this->module->currentOrder;
		    $this->pedido = new Order((int)$this->id_order);
			$this->module->updateOrderStatus($this->context->cart->id, (int)$this->cod_status, (int)$this->id_order);
		}
        if($this->pedido !== false) {
		    $this->endereco_final = 'index.php?controller=order-confirmation&id_cart='.$this->context->cart->id.'&id_module='.$this->module->id.'&id_order='.$this->id_order.'&key='.$this->context->cart->secure_key;
    	    $this->redirectFinal();
        }
    }

	/*
	 * Processa erro no retorno da API
	 * Redireciona o usuário para o carrinho novamente para retentativa
	 */
    private function retornoErroApi()
    {
		$pagseguro_msg = $this->module->l('Forma de pagamento temporariamente indisponível. Por favor, tente novamente ou escolha outra forma de pagamento.');
		$this->context->smarty->assign('pagseguro_msg',$pagseguro_msg);

		if(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc'){
			$this->endereco_final = 'index.php?controller=order-opc&isPaymentStep=true&pagseguro_msg='.htmlentities(urlencode($pagseguro_msg));
		}else{
			$this->endereco_final = 'index.php?controller=order&step=3&pagseguro_msg='.htmlentities(urlencode($pagseguro_msg));
		}
		$this->redirectFinal();
    }
    
	/*
	 * Redirecionamento do usuário 
	 * Confirmação do pedido ou Carrinho, em caso de erro ou negativa
	 */
    private function redirectFinal()
    {
		$status = (int)$this->cod_status;
		$buyer_ip = $this->module->getUserIp();
		$id_cart = (int)$this->context->cart->id;
		if(!$id_cart || (int)$id_cart < 1){
		    $id_cart = (int)$this->cart_id;
		}
		$cart = new Cart($id_cart);
		$id_order = (int)$this->id_order > 0 ? (int)$this->id_order : 0;
		if(!$id_order || (int)$id_order < 1){
		    $id_order = Order::getOrderByCartId($id_cart);
		}
		$cod_cliente = $this->context->cart->id_customer;
		if(!$cod_cliente || (int)$cod_cliente < 1){
		    $cod_cliente = $cart->id_customer;
		}
		$parcelas = (int)Tools::getValue('ps_cartao_parcelas');
		$this->insert_data = array(
			"id_shop" => (int)$this->context->shop->id,
			"id_order" => (int)$id_order > 0 ? (int)$id_order : 0,
			"id_cart" => (int)$id_cart > 0 ? (int)$id_cart : 0,
			"referencia" => $this->referencia,
			"cod_cliente" => (int)$cod_cliente,
			"cod_transacao" => (string)$this->cod_transacao, 
			"cpf_cnpj" => isset($this->doc) && $this->doc ? $this->doc : '',
			"buyer_ip" => $buyer_ip, 
			"status" => $status, 
			"desc_status" => $this->module->parseStatus($status),
			"pagto" => $this->paymentMethodType,
			"desc_pagto" => $this->module->parseTipoPagamento($this->paymentMethodCode),
			"parcelas" => isset($parcelas) && (int)$parcelas > 0 ? $parcelas : 1,
			"url" => isset($this->paymentLink) && $this->paymentLink != '' ? (string)$this->paymentLink : '', 
			"data_pedido" => date("Y-m-d h:i:s"),
			"data_atu" => date("Y-m-d h:i:s"),
		);
		$bd = $this->module->insertPagSeguroData($this->insert_data);
		if (!$bd) {
		    $this->module->saveLog('error', 'Inserir Dados', $this->context->cart->id, json_encode($transacao), 'Banco de Dados não atualizado.');
		}
		if (Configuration::get('PAGSEGUROPRO_MODO') == 0) {
			echo "<br/><br/><h3 style='color:red'><b>Iniciando debug...</b><br/><br/><b>Sistema executando em modo de TESTES!</b></h3><br/><br/>";
			Tools::p($this->autorizacao);
			Tools::p($this->insert_data);
			Tools::p($this->pedido);
			if (!$bd) {
				echo "<font color=\"red\"><b>Banco de Dados não atualizado!</b></font><br/><br/>";
			}
			echo "<font color=\"red\"><b>Finalizando debug!</b></font><br/><br/>";
			echo "<a class='btn btn-lg' href='".Tools::getShopDomainSsl(true,true).__PS_BASE_URI__.$this->endereco_final."'>Clique aqui e verifique se a página final do pedido será exibida corretamente</a><br/>";
		}else{
    		Tools::redirectLink($this->endereco_final);
		}
    }

}
