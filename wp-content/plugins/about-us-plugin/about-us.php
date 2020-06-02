<?php 

/**
 * Plugin Name: Keshab-About-us
 * Description: This is simple about us plugin using html5, bootstrap and php language
 */

 function example_about_plugin(){
     $content = '';

     $content .= '<p>Starbucks’ company profile has it all — the company’s mission, background story, products, store atmosphere, and even folklore regarding the name. Best of all, they somehow manage to pull off sounding both genuine and grandiose. I don’t know many other coffee stores that could claim, “our mission: to inspire and nurture the human spirit”. Starbucks’ company profile is a fantastic example of a store with a common household product — coffee — managing to stand out from the competition through their mission and values.

     We Provide IT services in Nepal which include Software development related to Hospital, School, HRM, ERP, Real Estate ERP, CRM, Account & Inventory. We are IT company in Nepal Providing Design & Development of Software, website, Digital Marketing service.
     
     At the core of our works, we offer risk-assessment services and decision analytics in the area of healthcare that help customers better understand and manage their risk.</p>';

     return $content;
 }
 add_shortcode( 'example_about', 'example_about_plugin' );

?>