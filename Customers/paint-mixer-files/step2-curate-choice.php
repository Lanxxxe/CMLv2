<?php
$valid_Curates = ["retreater", "weaver", "commoner", "brave"];
$curate = filter_input(INPUT_GET, "curate", FILTER_SANITIZE_SPECIAL_CHARS);
?>

<div class="main-container step2-container">
    <div class="progress-container">
        <?php
        include_once("./paint-mixer-files/steps-nav.php");
        ?>

        <div class="browse-colors-container">
            <a href="color-change.php?step=2b">
                < Explore Different Colors</a>
                    <h1 id="curate-Name"></h1>

                    <div class="curated-pallets-container">
                        <?php
                        $curate = filter_input(INPUT_GET, "curate", FILTER_SANITIZE_SPECIAL_CHARS);

                        if ($curate === 'retreater') {
                            echo '
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        <span class="retreater-spans"></span>
                        ';
                        } else if ($curate === 'weaver') {
                            echo '
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        <span class="weaver-spans"></span>
                        ';
                        } else if ($curate === 'commoner') {
                            echo '
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        <span class="commoner-spans"></span>
                        ';
                        } else if ($curate === 'brave') {
                            echo '
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        <span class="brave-spans"></span>
                        ';
                        }
                        ?>

                        <span class="retreater-span"></span><span class="retreater-span"></span><span class="retreater-span"></span><span class="retreater-span"></span><span class="retreater-span"></span><span class="retreater-span"></span><span class="retreater-span"></span><span class="retreater-span"></span>
                    </div>
        </div>
    </div>

    <?php include './paint-mixer-files/paint-order.php' ?>
</div>