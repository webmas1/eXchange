<?php

// Getting form values from ajax and return API results
if (isset($_GET["changeValue"])) {

    // db connect function
    require_once 'functions/functions.php';

    // values
    $changeValue = $_GET["changeValue"];
    $changeFrom = $_GET["changeFrom"];
    $changeTo = $_GET["changeTo"];

    if ($link = db_connect()) { // connect to DB
        foreach ($changeTo as $coin) {
            $sql = "SELECT * FROM currencies WHERE change_from = '$changeFrom' AND change_to = '$coin' AND updated_at = CURRENT_DATE()"; // select from db query
            $result = mysqli_query($link, $sql); // exec query

            if ($result && mysqli_num_rows($result) > 0) { // if line exists and up to date
                while($row = $result->fetch_assoc()) {
                    $currency = $row['currency']; // getting currency from db query
                }
                $results[$coin] = $changeValue * $currency; // calculate each value to coin currency & pushing each result into the array
            
            } else {

                $changeTo = implode(",",$_GET["changeTo"]); // array to string

                // API call with CURL
                $handle = curl_init();
                $url = "https://api.exchangerate.host/latest?base=$changeFrom&symbols=$changeTo";
            
                curl_setopt_array($handle, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true
                ]);
            
                if(curl_exec($handle)){
                    $currencies = curl_exec($handle); // results from API
                    $currencies = json_decode($currencies); // string to an object
                    $currencies = get_object_vars($currencies->rates); // gets the properties of the given object
                    foreach ($currencies as $coin => $currency) { // break object into separated coins and currencies
            
                        if ($link = db_connect()) { // connect to DB
            
                            $sql = "SELECT * FROM currencies WHERE change_from = '$changeFrom' AND change_to = '$coin'"; // select from db query
                            $result = mysqli_query($link, $sql); // exec query
            
                            if ($result && mysqli_num_rows($result) > 0) { // if line exists and not up to date
                                $sql = "UPDATE currencies SET currency = '$currency', updated_at = CURRENT_DATE() WHERE change_from = '$changeFrom' AND change_to = '$coin'"; // update db query
                                $result = mysqli_query($link, $sql); // exec query
            
                            } else { // if line doesn't exists
                                $sql = "INSERT INTO currencies (id, change_from, change_to, currency, updated_at) VALUES ('', '$changeFrom', '$coin', '$currency', CURRENT_DATE());"; // insert to DB query
                                $result = mysqli_query($link, $sql); // exec query
                            }
                        };
                        
                        $results[$coin] = $changeValue * $currency; // calculate each value to coin currency & pushing each result into the array
                    };
                };    
            
                curl_close($handle); // close curl
            };
        };
        echo json_encode($results); // print it to ajax
        die;
    };

};

// Getting coins list from API
$handle = curl_init();
$url = "https://api.exchangerate.host/symbols";

curl_setopt_array($handle, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true
]);

if(curl_exec($handle)){
    $coins = curl_exec($handle); // results from API
    $coins = json_decode($coins); // string to an object
    if($coins->success) {
        $coins = get_object_vars($coins); // gets the properties of the given object
        $coins = get_object_vars($coins['symbols']); // gets the properties of the given object
    } else {
        echo "Service is down, please try again later...";die;
    }
} else {
    echo "Something wen't wrong, please try again later...";die;
};    

curl_close($handle);

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- google font -->
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans&display=swap" rel="stylesheet">
    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <!-- font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css" integrity="sha256-BtbhCIbtfeVWGsqxk1vOHEYXS6qcvQvLMZqjtpWUEx8=" crossorigin="anonymous" />
    <!-- my styles -->
    <link rel="stylesheet" type="text/css" href="css/styles.css">

    <title>eXchange</title>
</head>

<body>
    <!-- HEADER -->
    <header class="sticky-top">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-auto m-2">
                    <img src="img/logo.png" alt="" class="logo" alt="eXchange">
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main>
        <div class="container-fluid py-5 px-2 px-md-5">
            <div class="row justify-content-around mx-2 mx-md-5">

                <!-- HEADLINE -->
                <div class="col-12 col-lg-10 col-xl-6 my-0 my-xl-5">
                    <h1 class="text-light text-center text-lg-left">eXchange Currency Convertor</h1>
                </div>

                <!-- EMPTY COL -->
                <div class="col-12 col-lg-10 col-xl-5 mt-3 mt-xl-0 pt-4 pb-3"></div>

                <!-- FORM COL -->
                <div class="col-12 col-lg-10 col-xl-6 p-0">
                    <div class="p-4 convertor-form">

                        <!-- form -->
                        <form>
                            <div class="form-row">

                                <!-- change value -->
                                <div class="col-sm-6 col-md-auto">
                                    <label for="change_value" hidden>Change Value</label>
                                    <input type="text" class="form-control form-control-lg border-0 rounded-0" id="change_value" name="change_value" placeholder="Change Value">
                                </div>

                                <!-- change from -->
                                <div class="col-sm-6 col-md-auto mt-2 mt-sm-0 pt-1 pb-1 pt-sm-0 pb-sm-0 dynamic-border">
                                    <label for="change_from" hidden>Change From</label>
                                    <select class="form-control form-control-lg border-0 rounded-0" id="change_from" name="change_from">
                                        <option value="" disabled selected hidden>Change From</option>
                                        <?php foreach($coins as $coin => $currency): ?>
                                        <option value="<?= $coin ?>"><?= $coin ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                            </div>
                            <div class="form-row mt-2 mt-sm-5">

                                <!-- change to -->
                                <div class="col-sm-5 col-lg-4">
                                    <label for="change_to" hidden>Change To</label>
                                    <select class="form-control form-control-lg border-0 rounded-0" id="change_to" name="change_to">
                                        <option value="" disabled selected hidden>Change To</option>
                                        <?php foreach($coins as $coin => $currency): ?>
                                        <option value="<?= $coin ?>"><?= $coin ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <!-- add button -->
                                <div class="col-5 col-sm-3 col-lg-2 mt-1 ml-2">
                                    <button type="button" onclick="addCoin()" class="btn text-light form-control-lg w-100" name="add">Add</button>
                                </div>

                                <!-- clear button -->
                                <div class="col-5 col-sm-3 col-lg-2 mt-1 ml-2">
                                    <button type="button" onclick="clearCoins()" class="btn text-light form-control-lg w-100" id="clear-btn" name="clear" disabled>Clear</button>
                                </div>

                                <!-- coins list -->
                                <div class="col-10 pl-3 pl-sm-0 ml-0 ml-sm-4 mt-2">
                                    <p class="coins-list text-secondary pl-0"></p>
                                    <small class="limit-alert text-danger"></small>
                                </div>

                            </div>

                            <!-- submit button -->
                            <div class="form-row mt-3 mt-sm-5">
                                <button type="button" onclick="showValues()" class="btn text-light font-weight-bold ml-2 form-control form-control-lg" name="submit">Show Change Values</button>
                                <small class="form-alert text-danger mx-auto my-2"></small>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- RESULTS COL -->
                <div class="col-12 col-lg-10 col-xl-5 mt-3 mt-xl-0 p-0">
                    <div class="table-responsive px-2 pt-4 pb-3 convertor-table d-none">

                        <!-- table of results -->
                        <table class="table table-sm table-borderless text-center">
                            <thead>
                                <tr>
                                    <th class="w-25">Change<br>From</th>
                                    <th class="w-25">Change<br>To</th>
                                    <th class="w-25">Change<br>Rate</th>
                                    <th class="w-25">Value</th>
                                </tr>
                            </thead>
                            <tbody class="results">
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-auto m-2">
                    <p class="m-0">All rights reserved &copy; <img src="img/logo.png" alt="" class="logo" alt="eXchange"></p>
                </div>
            </div>
        </div>
    </footer>




    <!--  SCRIPTS  -->

    <!-- jquery js -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    
    <!-- popper js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    
    <!-- bootstrap js -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- my scripts -->
    <script src="js/scripts.js"></script>

</body>

</html>