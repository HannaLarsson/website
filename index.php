<!DOCTYPE html>
<html>

<?php echo file_get_contents("html/header.html"); ?>
</style>
<?php echo file_get_contents("style.css"); ?>
</style>

<?php
$content = file_get_contents("https://dev14.ageraehandel.se/sv/api/product");
$result = json_decode($content);                                              
$result_array = json_decode($content, true);                                  

$products = $result->products;                                                

$test = json_encode($products);
$products_array = json_decode($test, true);

/* Compare strings, help function for usort.*/
function cmp_name($a,$b) {
    $has_name_a = array_key_exists('artiklar_benamning', $a);
    $has_name_b = array_key_exists('artiklar_benamning', $b);

    if ($has_name_a && $has_name_b){
        return strnatcasecmp($a['artiklar_benamning'] ,$b['artiklar_benamning']);
    }
    else if (! $has_name_a){
        return strnatcasecmp("Inget namn" ,$b['artiklar_benamning']);
    }
    else{
        return strnatcasecmp($a['artiklar_benamning'], "Inget namn");
    }
}

usort($products_array, "cmp_name");

$number_of_products = count($products_array);
?>


<body>
    <h2>Väkommen! Vi kan erbjuda följande <?php echo $number_of_products ?> artiklar: </h2>

<?php 

/* A product should have a name, price, currency, category, balance and VAT. */
class Product{

    private $name;
    private $price;
    private $category;
    private $balance;
    private $VAT;
    private $currency;

    public function __construct($name, $price, $currency, $category, $balance, $VAT){
        $this->name = $name;
        $this->price = $price;
        $this->currency = $currency;
        $this->category = $category;
        $this->balance = $balance;
        $this->VAT = $VAT;
    }

    /* Return the name. */
    public function get_name(){
        return $this->name;
    }
    /* Return the price. */
    public function get_price(){
        return $this->price;
    }

    /* Return the currency. */
    public function get_currency(){
        return $this->currency;
    }

    /* Return the category. */
    public function get_category(){
        return $this->category;
    }
    /* Return the balance. */
    public function get_balance(){
        return $this->balance;
    }

    /* Return the VAT. */
    public function get_VAT(){
        return $this->VAT;
    }

    /* Return true if the product's balance is positive, otherwise false.*/
    public function positive_balance(){
        if ($this->balance > 0){
            return True;
        }
        else {
            return False;
        }
    }
}

/* Return an array of the categories in the product list. If one or more products do not have a category, 
    a category called "ÖVRIGT" is added. 
*/
function create_category_array($number_of_products, $products_array){
    $category_array = array();
    $category_array_final = array();

    for($x = 0; $x < $number_of_products; $x++){
        $has_category = array_key_exists('artikelkategorier_id', $products_array[$x]);

        if($has_category){

            array_push($category_array, $products_array[$x]['artikelkategorier_id']);
        }
        else{
            array_push($category_array, "ÖVRIGT");
        }
    }
    $unique = array_unique($category_array);
    foreach($unique as $item){
        array_push($category_array_final, $item);
    }
    return $category_array_final;
}

$category_array = create_category_array($number_of_products, $products_array);

/* Return an array of product objects given an array of products. 
    If a product is missing a key or value, set it to "NULL".*/
function create_products_array($products_array){
    $array = array();

    foreach($products_array as $product){
        $has_name = array_key_exists('artiklar_benamning', $product);
        $has_price = array_key_exists('pris', $product);
        $has_balance = array_key_exists('lagersaldo', $product);
        $has_VAT = array_key_exists('momssats', $product);
        $has_currency = array_key_exists('valutor_id', $product);
        $has_category = array_key_exists('artikelkategorier_id', $product);

        if ($has_name){
            $name = $product['artiklar_benamning'];
        }
        else{
            $name = "NULL";
        }

        if ($has_currency){
            $currency = $product['valutor_id'];
        }
        else{
            $currency = "NULL";
        }

        if ($has_price){
            if ($has_VAT){
                $VAT = $product['pris'] * $product['momssats']/100;
                $price = $product['pris'] + $VAT;
            }
            else{
                $price = "NULL";
            }
        }
        else {
            $price = "NULL";
            $VAT = $product['pris'] * $product['momssats']/100;
        }

        if ($has_balance){
            $balance = $product['lagersaldo'];
        }
        else{
            $balance = "NULL";
        }

        if($has_category){
            $category = $product['artikelkategorier_id'];
        }
        else{
            $category = "ÖVRIGT";
        }

        $product_obj = new Product($name, $price, $currency, $category, $balance, $VAT);
        array_push($array, $product_obj);
    }
    return $array;
}

/* */
function create_price_array($products_array){
    $array = array();
    foreach($products_array as $prod){
        array_push($array, $prod->get_price());
    }
    return $array;
}

$array_of_products = create_products_array($products_array);

$counter = 1;
foreach($category_array as $cate){
    print_r("<table><th></th><th>Produkt</th><th>Pris (inkl. moms)</th><th>I lager</th></tr>");

    print_r("<h3>" . $cate . "</h3>");
    $trtd = "<tr><td>";
    $tdtd = "</td><td>";

    
    foreach($array_of_products as $prod){
        if($prod->get_category() == $cate)  {
            if($prod->positive_balance()){
                $positive_balance = "Ja";
            }else{
                $positive_balance = "Nej";
            }
            print_r($trtd . "<p>" . $counter . "</p>" . $tdtd . "<p>" . $prod->get_name() . "</p>" . $tdtd . "<p>" . $prod->get_price() . " " . $prod->get_currency() . "</p>" . $tdtd . "<p>" . $positive_balance . "</p></td></tr>");
            $counter++;
        }
    }
    echo "<hr></tr></table>";
}

$price_array = create_price_array($array_of_products);

print_r("<br> Priserna varierar mellan: " . min($price_array) . " till " . max($price_array) . " kr.");
?>


</body>
</html>

<?php echo file_get_contents("html/footer.html"); ?>