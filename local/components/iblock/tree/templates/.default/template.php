<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
function printTree($tree, $level = 1) {
    foreach ($tree as $section) {
        echo '<div>'. str_repeat('-', $level * 2) . $section['NAME'] . "</div>";
        if (!empty($section['ELEMENTS'])) {
            foreach ($section['ELEMENTS'] as $element) {
                echo '<div>'. str_repeat('-', $level * 2 + 2) . $element['NAME'] .' ('.$element['TAGS'].')'. "</div>";
            }
        }
        if (!empty($section['CHILDREN'])) {
            printTree($section['CHILDREN'], $level + 1);
        }
    }
}
if (!empty($arResult)):?>
<section class="tree">
   <?php printTree($arResult);?>
</section>
<?php endif; ?>