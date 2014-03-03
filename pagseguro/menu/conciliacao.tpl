<h2>{$titulo}</h2>
<br>
<label>DIAS</label>
<br>
    <select id='pagseguro_dias' name='pagseguro_dias' class='select' style='width:100px !important'>
    {$dias}
    </select>
<input class="pagseguro-button green-theme normal" type='button' name='search' value='Pesquisar'/>
<br>
<br>
<table id='htmlgrid' class='gridConciliacao' width='100%'>
    <thead>
        <tr>
            <th>Data</th>
            <th>ID PrestaShop</th>
            <th>ID PagSeguro</th>
            <th>Status PrestaShop</th>
            <th>Status PagSeguro</th>
            <th>Editar</th>
            <th>Modificar</th>
        </tr>
    </thead>
    <tbody id="resultTable">
        {$tableResult}
    </tbody>
</table>
<input type='hidden' id='adminToken' value='{$adminToken}'>
<br>
<div class="col-md-12 text-center">
    <ul class="pagination pagination-lg pager" id="myPager"></ul>
</div>
