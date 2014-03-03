<h2>{$titulo}</h2>
<br>
<div>
    <label>URL DE NOTIFICAÇÃO</label>
    <br>
        <input type="text" name="pagseguro_notification_url" id="pagseguro_notification_url" value="{$urlNotification}" maxlength="255" hint="Sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo.">
    <br>
    <label>URL DE REDIRECIONAMENTO</label>
    <br>
        <input type="text" name="pagseguro_url_redirect" id="pagseguro_url_redirect" value="{$urlRedirection}" maxlength="255" hint="Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar neste campo. Para utilizar essa funcionalidade você deve configurar sua conta para aceitar somente requisições de pagamentos gerados via API. &lt;a href=&quot;https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml&quot; target=&quot;_blank&quot;&gt; Clique aqui &lt;/a&gt; para ativar este serviço.">
    <br>
    <label>LOG</label>
    <br>
        <select id="pagseguro_log" name="pagseguro_log" class="select" hint="Deseja habilitar a geração de log?" >
        {$optionLog}
        </select>
    <br>
    <div id="directory-log" name="directory-log">
    <label>DIRETÓRIO</label>
    <br>
        <input type="text" name="pagseguro_log_dir" id="pagseguro_log_dir" value="{$fileLocation}" hint= "Diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_ps.log"/>
    <br>
    </div>
    <label>RECUPERAR CARRINHO</label>
    <br>
        <select id="pagseguro_recovery" name="pagseguro_recovery" class="select" hint="Deseja habilitar a recuperação de carrinho?" >
            {$optionRecovery}
        </select>
    <br>
    <div id="directory-val-link" name="directory-val-link">
    <label>VALIDADE DO LINK</label>
    <br>
        <select id="pagseguro_days_recovery" name="pagseguro_days_recovery" class="select" hint="Quantidade de dias que o link de recuperação será válido." >
            {$validLink}
        </select>
    <br>
    </div>
    <div class="hintps _config"></div>
</div>

<script type="text/javascript">
    {literal}
        if ($("select[name=pagseguro_log] option:selected").val() == 0) {
            $("#directory-log").toggle(300);
        }
        if ($("select[name=pagseguro_recovery] option:selected").val() == 0) {
            $("#directory-val-link").toggle(300);
        }
    {/literal}
</script>