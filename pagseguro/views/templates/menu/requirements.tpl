<style>
ul, li {
    list-style-type: none;
}
br { line-height: 5px; }
</style>

<h2>{$titulo}</h2>
<br />
<ul>
    {foreach from=$error item=erro}
    	<li><img src='{$erro[0]}'> {$erro[1]}</li>
    {/foreach}
</ul>

