<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CoronaVirus extends CI_Controller 
{
    private $url = "https://coronavirus-19-api.herokuapp.com/countries";

    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $data['RESULT_DATA'] = array();

        $contents = file_get_contents($this->url);
        if ($contents === false)  $resultJSON = NULL;
        else    $data['RESULT_DATA'] = json_decode($contents,true);
        
        $geochart_country_update = array( "USA"=>"US", "UK"=>"United Kingdom", "S. Korea"=>"South Korea");
        $data['RESULT_DATA'] = $this->change_country($data['RESULT_DATA'],$geochart_country_update);
        
        $data['TOTAL_CASE_REPORTED'] = array_sum(array_column($data['RESULT_DATA'],'cases'));
        $data['TOTAL_DEATHS_REPORTED'] = array_sum(array_column($data['RESULT_DATA'],'deaths'));          
        $data['TOTAL_RECOVERED_REPORTED'] = array_sum(array_column($data['RESULT_DATA'],'recovered'));
        $data['TOTAL_CASE_REPORTED_LASTDAY'] = $data['TOTAL_CASE_REPORTED'] - array_sum(array_column($data['RESULT_DATA'],'todayCases'));

        $data['MAP'] = $data['RESULT_DATA'];
        usort($data['RESULT_DATA'], array($this,'cmp')); //asc
        $data['RESULT_DATA'] = $this->number_format_array($data['RESULT_DATA']);

        $this->load->view('coronavirus_vw',$data);
    }

    private function change_country($data,$geochart_country_update)
    {
        $i = 0;
        foreach($data as $each){
           if(array_key_exists($each['country'], $geochart_country_update)){
                $data[$i]['country'] = $geochart_country_update[$each['country']];
            }
            $i++;  
       }

       return $data;
    }

    private function number_format_array($data){
        $keys = array_keys($data[0]);
        for($i=0;$i<count($data);$i++){
            foreach($keys as $key){
                $data[$i][$key] = is_numeric($data[$i][$key]) ? number_format($data[$i][$key]) : $data[$i][$key];
            }
        }
    
        return $data;
    }

    function cmp($a, $b)
    {
        if ($a['cases'] == $b['cases']) {
            return 0;
        }
        return ($a['cases'] < $b['cases']) ? 1 : -1;
    }
}