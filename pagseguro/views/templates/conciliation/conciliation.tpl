
<h2>{$titulo}</h2>

<p style="text-align:justify"><small>Esta consulta permite obter as transações recebidas por você em um intervalo de datas. Ela pode ser usada periodicamente para verificar se o seu sistema recebeu todas as notificações de transações enviadas pelo PagSeguro, de forma a conciliar as transações 
armazenadas em seu sistema com o PagSeguro.</small></p>

<input type='hidden' id='adminToken' value='{$adminToken}' />
<input type='hidden' id='urlAdminOrder' value='{$urlAdminOrder}' />

{if (!$regError)}

    <label style="float: none;">DIAS</label>
    <br />
        <select id='pagseguro_dias_btn' name='pagseguro_dias' class='select' style='width:100px !important;'>
            {$dias}
        </select>
    <input class="pagseguro-button green-theme normal" type='button' name='search' value='Pesquisar' style="margin-top: -3px;" />

    <br />
    
    <table id='htmlgrid' class='gridConciliacao' cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Data</th>
                <th>ID PrestaShop</th>
                <th>ID PagSeguro</th>
                <th>Status PrestaShop</th>
                <th>Status PagSeguro</th>
                <th>Editar</th>
                <th>Atualizar</th>
            </tr>
        </thead>
        <tbody id="resultTable">
            {$tableResult}
        </tbody>
    </table>
    <br /><br /><br />


    <script type="text/javascript">
        {literal}

            jQuery(document).ready(function(){ 
              var flow = 0;  
              jQuery('#htmlgrid').dataTable(
                {       

                    "info": false,
                    "lengthChange": false,
                    "searching": false,
                    "pageLength": 10,
                     "aoColumnDefs": [
                           { 'bSortable': false, 'aTargets': [ 5, 6 ] },
                           { "sClass": "tabela", 'aTargets': [ 1, 2, 3, 4, 5, 6 ] }
                       ],
                   "oLanguage": {
                        "sEmptyTable":"Nenhuma transação a ser conciliada, realize uma pesquisa ou contate o adminstrador em caso de erros.",
                        "oPaginate": {
                            "sNext": 'Próximo',
                            "sLast": 'Último',
                            "sFirst": 'Primeiro',
                            "sPrevious": 'Anterior'
                         }
                    },

                    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                            if ( aData[3] == aData[4])
                            {
                                jQuery(nRow).css('color', 'green')
                                jQuery(nRow).css('fontSize', '12px')
                                jQuery(nRow).css('textAlign', 'center')
                            } else {
                                jQuery(nRow).css('color', 'red')
                                jQuery(nRow).css('fontSize', '12px')
                                jQuery(nRow).css('textAlign', 'center')
                            }
                    },

                    "fnDrawCallback": function(oSettings) {

                        if (!flow) {
                            if(jQuery('#htmlgrid tr').length < 10){
                                document.getElementById("htmlgrid_paginate").style.display="none";
                            } else {
                                document.getElementById('htmlgrid_paginate').style.display = "block";
                                flow = 1;
                            }
                        }
                           
                        
                    }    

                });
            });

        {/literal}
    </script>

    <p class='info' style="text-align: center;">Não encontra suas antigas transações para conciliar? 
        <a data-tooltip='Na instalação do módulo do PagSeguro é criada uma referência com cinco (5) caracteres aleatórios por exemplo - #asf9 - que serão enviados na hora da compra. Caso não exista a referência de sua loja o PagSeguro não irá retornar compras a serem conciliadas.'>
            <img src='../img/admin/help.png' alt='ajuda' />
        </a>
    </p>
  
{else}
    <div class="warn">
        <p class="small text-center">Para conciliar transações é necessário configurar um email e token válidos.</p>
    </div>

    <table id="htmlgrid" cellspacing="0" width="100%"></table>
{/if}


