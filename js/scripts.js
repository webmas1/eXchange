if (coinsList = localStorage.getItem("coinsList")) { // if exists - get list from local storage
    $(".coins-list").text(coinsList); // print it
    coinsList = coinsList.split(", "); // turn string to array
    $("#clear-btn").prop('disabled', false); // enable clear button
} else {
    var coinsList = new Array;
}

////////// Add a coin to the list //////////

function addCoin() { // On click ADD
    if (coinsList.length < 10) { // if user hasn't reached his limit
        var coin;
        if ((coin = $("#change_to").val()) && ($.inArray(coin, coinsList) === -1)) { // getting coin value from the form and checking if in array
            coinsList.push(coin); // pushing coin to array
            $(".coins-list").text(coinsList.join(", ")); // writes all array to html
            $("#clear-btn").prop('disabled', false); // enable clear button
            $(".limit-alert").html(""); // reset limit alert msg
        } else if (($("#change_to").val()) && ($.inArray(coin, coinsList) !== -1)) { // if coin already in the array
            $(".limit-alert").text("Coin is already on the list");
        } else { // no coin has been chosen
            $(".limit-alert").text("Please choose a coin from the list");
        }
    } else { // user reached his limit
        $(".limit-alert").text("You can choose only up to 10 coins");
    }
};



////////// Clear coins from the list //////////

function clearCoins() { // On click CLEAR
    if (coinsList.length > 0) {
        coinsList.length = 0; // empty array
        localStorage.removeItem("coinsList"); // remove from local storage
        $("#clear-btn").prop('disabled', true); // disable the clear button
        $(".coins-list").text(""); // clear coins list
        $(".limit-alert").text(""); // clear limit alert
        $('#change_to option').prop('selected', function() { // clear change to select option
            return this.defaultSelected;
        });
        $(".results").html(""); // clear results from table
        $(".convertor-table").addClass("d-none"); // hide table
    }
}


////////// Convert Currencies //////////

function showValues() {
    $(".results").html("");
    // Validate & getting values
    var changeValue = $("#change_value").val();
    changeValue = changeValue.split(' ').join(''); // removing spaces
    changeValue = changeValue.split(',').join(''); // removing commas

    var numbers = /^\d+(\.\d{1,2})?$/; // regular expression for valid value
    
    if (!numbers.test(changeValue)) { // if value not valid
        $(".form-alert").text("Change value must be up to 2 decimal digits");
    } else if (!$("#change_from").val()) { // if change from empty
        $(".form-alert").text("Missing coin to change from");
    } else if (coinsList.length === 0) { // if change to is empty
        $(".form-alert").text("Missing coins to change to");
    } else { // if everything fulfilled
        $(".form-alert").text(""); // empty form alert
        var changeFrom = $("#change_from").val();
        var changeTo = coinsList;

        // coinsList = $.stringify(coinsList);
        localStorage.setItem("coinsList", coinsList.join(", ")); // save on local storage

        // ajax call to get currencies by selected coin
        $.ajax({
            url: "index.php", // send to
            data: { // pass form values to CURL function on php file
                changeValue: changeValue,
                changeFrom: changeFrom,
                changeTo: changeTo
            },
            success: function(results){ // return results
                results = $.parseJSON(results); // turn json string into JS object
                
                $.each(results, function(index, value){ // each-loop on results
                    changeRate = value / changeValue; // calculation of change rate
                    changeRate = changeRate.toFixed(2); // 2 digits pass dot
                    value = value.toFixed(2); // 2 digits pass dot
                    
                    $(".results").append("<tr><td>" + changeFrom + "</td><td>" + index + "</td><td>" + changeRate + "</td><td>" + value + "</td></tr>"); // writes on table each result
                })
          }});
        $(".convertor-table").removeClass("d-none"); // making table visible
    }

}
