<?php

EbscoKbService::setSearch($_POST['search']);
$params = EbscoKbService::getSearch();

// Don't run a empty title query if no package limit is set
if(empty($params['search']) && $params['type'] == 'titles' && empty($params['packageId'])){
    echo '<div style="margin: 2em;"><i>Please enter a search term.</i></div>';
    exit;
} else {
    $ebscoKb = new EbscoKbService($params);
    $ebscoKb->execute();
}

if($ebscoKb)
// check for results
$totalRecords = $ebscoKb->numResults();
$items = $ebscoKb->results();
if(empty($totalRecords) || empty($items)){
    echo '<div style="margin-bottom: 2em;"><i>No results found.</i></div>';
    exit;
}

// Pagination vars
$page = $ebscoKb->queryParams['offset'];
$recordsPerPage = $ebscoKb->queryParams['count'];
$numPages = ceil($totalRecords / $recordsPerPage);
$maxDisplay = 25;
$pagination = [];
$halfMax = floor($maxDisplay/2);
$i = $page + $halfMax > $numPages ? $page - ($maxDisplay - ($numPages - $page + 1)) : $page - floor($maxDisplay/2);
while(count($pagination) <= $maxDisplay){
    if ($i > $numPages){
        break;
    }
    if($i > 0){
        $pagination[] = $i;
    }
    $i++;
}
$fromCalc = $recordsPerPage * ($page - 1) + 1;
$toCalc = ($fromCalc - 1) + $recordsPerPage;
$toCalc = $toCalc > $totalRecords ? $totalRecords : $toCalc;

// Limited by vendor?
if(!empty($params['vendorId'])){
    $ebscoKb = new EbscoKbService();
    $vendor = $ebscoKb->getVendor($params['vendorId']);

    if(!empty($params['packageId'])){
        $package = $ebscoKb->getPackage($params['vendorId'], $params['packageId']);
    }
}



?>

<?php if(!empty($vendor) && empty($package)): ?>
    <div>
        <h2>
            Packages from <?php echo $vendor->vendorName; ?>
            <small style="padding-left: 1px">(<?php echo $vendor->packagesSelected; ?> of <?php echo $vendor->packagesTotal; ?> selected)</small>
        </h2>
    </div>
<?php endif; ?>

<?php if(!empty($vendor) && !empty($package)): ?>
    <div>
        <h2>
            Title list from <?php echo $package->packageName; ?><br />
            <small style="padding-left: 5px;">Vendor: <?php echo $vendor->vendorName; ?></small>
        </h2>
    </div>
<?php endif; ?>

<span style="float:left; font-weight:bold; width:650px;">
    Displaying <?php echo $fromCalc; ?> to <?php echo $toCalc; ?> of <?php echo $totalRecords; ?> results
</span>

<?php if ($totalRecords > $recordsPerPage): ?>
    <div style="vertical-align:bottom;text-align:left;clear:both;" class="pagination">
        <?php if($page == 1): ?>
            <span class="smallerText"><i class="fa fa-backward"></i></span>
        <?php else: ?>
            <a href="javascript:void(0);" data-page="<?php echo $page - 1; ?>" class="setPage smallLink" alt="previous page" title="previous page">
                <i class='fa fa-backward'></i>
            </a>
        <?php endif; ?>


        <?php foreach($pagination as $p): ?>
            <?php if ($p == $page): ?>
                <span class="smallerText"><?php echo $p; ?></span>
            <?php else: ?>
                <a href='javascript:void(0);' data-page="<?php echo $p; ?>" class="setPage smallLink"><?php echo $p; ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($page + 1 > $numPages): ?>
            <span class="smallerText"><i class="fa fa-forward"></i></span>
        <?php else: ?>
            <a href="javascript:void(0);" data-page="<?php echo $page+1; ?>" class="setPage smallLink" alt="next page" title="next page">
                <i class='fa fa-forward'></i>
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div style="vertical-align:bottom;text-align:left;clear:both;"></div>
<?php endif; ?>

<?php

switch($params['type']){
    case 'titles':
        include_once __DIR__.'/../templates/ebscoKbTitleList.php';
        break;
    case 'vendors':
        include_once __DIR__.'/../templates/ebscoKbVendorList.php';
        break;
    case 'packages':
        include_once __DIR__.'/../templates/ebscoKbPackageList.php';
        break;
    case 'holdings':
        echo '<pre>';
        echo print_r($items);
        echo '</pre>';
        break;
}
