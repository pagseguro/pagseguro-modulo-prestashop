<h2>{$titulo}</h2>
<br>
<table>
    <tr>
        <td><img src='{$error['curl'][0]}'></td>
        <td>{$error['curl'][1]}</td>
    </tr>
    <tr>
        <td><img src='{$error['dom'][0]}'></td>
        <td>{$error['dom'][1]}</td>
    </tr>
    <tr>
        <td><img src='{$error['spl'][0]}'></td>
        <td>{$error['spl'][1]}</td>
    </tr>
    <tr>
        <td><img src='{$error['version'][0]}'></td>
        <td>{$error['version'][1]}</td>
    </tr>
    <tr>
        <td><img src='{$error['moeda'][0]}'></td>
        <td>{$error['moeda'][1]}</td>
    </tr>
</table>