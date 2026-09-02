<?php

declare(strict_types=1);

?>

<table
    role="presentation"
    cellpadding="0"
    cellspacing="0"
    border="0"
>
    <tr>
        <td
            align="center"
            bgcolor="#111111"
            style="
                border-radius: 8px;
            "
        >
            <a
                href="<?= htmlspecialchars(
                    $urlBoton
                ) ?>"
                target="_blank"
                style="
                    display: inline-block;
                    padding: 14px 20px;
                    font-size: 15px;
                    line-height: 1;
                    font-weight: 700;
                    color: #ffffff;
                    text-decoration: none;
                "
            >
                <?= htmlspecialchars(
                    $textoBoton
                ) ?>
            </a>
        </td>
    </tr>
</table>