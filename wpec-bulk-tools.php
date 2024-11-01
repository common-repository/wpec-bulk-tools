<?php

/*  Copyright 2008  TODD HALFPENNY  (email : todd@gingerbreaddesign.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: WP e-Commerce Bulk Tools
Plugin URI: http://gingerbreaddesign.co.uk/wordpress/plugins/wpec-bulk-tools.php
Description: Enables bulk manipulation for products associated with the 'wp e-Commerce' plugin
Author: Todd Halfpenny
Version: 0.0.3
Author URI: http://gingerbreaddesign.co.uk/todd
*/


// Hooks and admin menu setup
add_action('admin_menu', 'add_options_pg');  

function add_options_pg() {
  add_submenu_page('wpsc-sales-logs','WPEC - Bulk Price Update','Bulk Price Update', 7, 'wpec-bulk-uploader', 'price_options');
}


/*#####################################################
  price_options
##################################################### */
function price_options() {
  echo '<div class="wrap">
    <div id="icon-tools" class="icon32"></div>
    <h2>wp e-Commerce: Bulk Price Updater</h2>';
  
  if (isset($_POST['pu_submit1'])) {
    $cat_Id = $_POST['category_parent'];
    $new_price = $_POST['pu_new_price'];
    if ($cat_Id > 0){
      $update_res = update_price($cat_Id, $new_price);
      if ($update_res == ""){
        echo "<div id='message' class='updated fade'><p>Category price updated!</p>
          <p>New price for category $cat_Id is &pound;$new_price</p></div>";
      }
      else {
        echo "<div id='message' class='error fade'><p>Error!</p>
        <p>$update_res</p></div>"; 
      }
    }
    else {
      echo "<div id='message' class='error fade'><p>Error!</p>
        <p>No category was selected. No product prices have been updated</p></div>"; 
    }
  }
  elseif (isset($_POST['pu_submit2'])) {
    update_price_by_csv();
    //echo "<div id='message' class='updated fade'><p>Category price updated!</p><p>$update_res</p>";
  }
  else {
    // show update form
    ?>
    <h2>Update Price by Category</h2>
    <p>This management facility can be used to update the product price for <strong>all</strong> products in a particular group.</p>
    <form name'form1' method='post' action='<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>'>
      <ul>
      <li>
        <label for='pu_cat_id'>Category</label>
        <?php echo wpsc_parent_category_list(1, 0,0); ?>
      </li>
      <li>
        <label for='pu_new_price'>New Price</label>
        <input type='textbox' name='pu_new_price' />
      </li>
      </ul>
      <input type='submit' name='pu_submit1' value='Update Price' class="button-primary" />
    </form>
    <h2>Update Price via File Upload</h2>
    <p>You can upload a <abbr title="Comma seperated values">CSV</abbr> file in the following format. Products will be matched against their <abbr title="Stock Keeping Unit">SKU</abbr> and the price <strong>only</strong> shall be updated</p>
    <p>File lines need to be in the form <strong>name,sku,price</strong>
    <form name'form2' enctype="multipart/form-data"  method='post' action='<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>'>
      <label for='pu_file'>CSV File</label>
      <input type='file' name='pu_file' />
      <br/>
      <input type='submit' name='pu_submit2' value='Update Prices' class="button-primary" />
    </form>
    <?php
  }
  
  echo "</div>";
}


/*#####################################################
  update_price_by_csv
##################################################### */
function update_price_by_csv(){
  global $wpdb;
  if($_FILES['pu_file']['tmp_name']) {
    $myFile = $_FILES['pu_file']['tmp_name'];
    $fh = fopen($myFile, 'r') or exit("Unable to open file!");
    $errStr = '';
    $resultStr = '';
    $prodResultStr = '';
    $prod_cnt = 0;
    while(!feof($fh))
      {
      $theDataArr = explode(",", fgets($fh), 3);
      $prod_name = trim($theDataArr[0]);
      $prod_sku = trim($theDataArr[1]);
      $prod_price = trim($theDataArr[2]);
      $select_sql = "SELECT product_id FROM `" .$wpdb->prefix. "wpsc_productmeta` WHERE (`meta_key` = 'sku' AND `meta_value`  = '" . $prod_sku . "')";
      $prod_to_update = $wpdb->get_results($select_sql, ARRAY_A);
      if($prod_to_update != null) {
        foreach($prod_to_update as $product) :
          $update_sql = "UPDATE `" . $wpdb->prefix .  "wpsc_product_list` 
          SET `price` = '" . $prod_price . "' 
          WHERE `id` = '" . $product['product_id'] . "'";
          $wpdb->query($update_sql);
          $prod_cnt ++;
          $prodResultStr .= "<li>Product " .  $product['product_id'] . " updated to have price $prod_price</li>";
        endforeach;
        }
      else {
        $errStr .= "<li><strong>Err</strong>: No product found with <abbr title='Stock Keeping Unit'>SKU</abbr> <strong>$prod_sku</strong></li>";
        }
      }
    fclose($fh);
    if($errStr == ''){
      $resultStr = "<div id='message' class='updated fade'><h3>Summary</h3><p>$prod_cnt product prices updated!</p><p>There were <strong>no</strong> errors</p><h3>Detailed Results</h3><ul>$prodResultStr</ul>";
    }
    else {
      $resultStr = "<div id='message' class='error fade'><h3>Summary</h3><p>$prod_cnt product prices updated!</p><p>There were <strong>some</strong> errors</p><h3>Detailed Results</h3><ul>$prodResultStr</ul><ul>$errStr</ul>";
    }
    echo $resultStr;
  } else{
      echo "<div id='message' class='error fade'><p>Error!</p>
        <p>The file could not be uploaded</p></div>"; 
  }
}

/*#####################################################
  update_price
##################################################### */
function update_price($cat_Id, $price){
  global $wpdb;
  $res = "";
  
  // run through product/cat table and update each product entry for the cat $cat_Id
  $sql = "SELECT * FROM `" .$wpdb->prefix. "wpsc_item_category_assoc` WHERE `category_id` = '" .$cat_Id. "'";
  $prods_to_update = $wpdb->get_results($sql, ARRAY_A);
  if($prods_to_update != null) {
    foreach($prods_to_update as $product) :
      $sql2 = "UPDATE `" . $wpdb->prefix .  "wpsc_product_list` 
      SET `price` = '" . $price . "' 
      WHERE `id` = '" . $product['product_id'] . "'";
      $wpdb->query($sql2);
    endforeach;
  }
  else {
    $res = 'No products were found in category ' . $cat_Id . '<br/>SQL-> ' . $sql;
  }
  
  return $res;
}

?>