<?php (defined('BASEPATH')) OR exit('No direct script access allowed');


class Books extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->load->model('Book');
        $this->load->model('categories/category');
        //$this->load->model('Product');
        $this->load->library('Paypal_lib');
        $this->load->library('email');
        // $config['protocol']='smtp';
        // $config['smtp_host']='home689761135.1and1-data.host';
        // $config['smtp_port']='22';
        // $config['smtp_timeout']='30';
        // $config['smtp_user']='info@LivingLinks.City'; //enter your user name [example@gmail.com]
        // $config['smtp_pass']='bismillah'; // password
        // $config['authentication']='plain';
        $config['charset']='utf-8';
        $config['newline']="\r\n";
        // $config['crlf'] = "\r\n";
        // $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        //$this->output->section('header', 'booking/header');
        //$this->output->section('sidebar', 'welcome/sidebar');
        //$this->output->section('footer', 'booking/footer');
        //$this->output->js('assets/themes/pages/vendor/jquery/jquery.min.js');  
        
        

        $query=$this->db->get('site_config')->result();
        $this->db_config=new stdClass();

        foreach ($query as $conf) {
            $key=$conf->config_name;
            $this->db_config->$key=$conf->value;
        }

        $this->secret ='secret'; // Share Secret
        $this->currency ='USD'; // Currency
        $this->merchant_id ='softgalaxy'; // Merchant Id
        $this->url ='https://pay.sandbox.realexpayments.com/pay'; // Merchant Id
    }

    function index($queue=0) {
        if ( !$this->session->userdata('logged_in')) {
            redirect(base_url('users/auth'));
        }
        //For Listing
        if($this->session->userdata('logged_in')){
            $data['ad_id']=$this->uri->segment(3);
            $appo_date_time = $this->input->get('appo_date_time');
                $data['appo_date_time']=$appo_date_time;
            
            if($data['ad_id'] && $data['appo_date_time']){

                $this->output->section('header', 'welcome/header');
                $this->output->section('sidebar', 'welcome/sidebar');
                $this->output->section('footer', 'welcome/footer');
                $this->output->set_template('admin');
                $login_id=$this->session->userdata('logged_in')->id;
                //$data['queues']=$this->Book->get_queue($login_id);
                
                //$rosterstartdate = date('Y-m-d H:i:s', strtotime($rosterstartdate));
                
                $data['booking_person_details']=$this->Book->fetch_booking_person($data['ad_id'],$appo_date_time);
               // print_r($data['booking_person_details']);
                // echo'<pre>';
                // print_r($data['booking_person_details']);
                // die;
                $data['booking_person_count']=count($data['booking_person_details']);
                //die;
                
                $queue=$this->Book->check_queue_exist($data['ad_id'],$appo_date_time);
                if($queue){
                    $data['queues']=json_decode($queue->booking_person_details)->queue_position;
                }else{
                    $data['queues']=0;
                }
                $data['count']=4;
                $this->output->set_title('Guest Management');
                $this->output->css('assets/themes/admin/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css');
                $this->output->js('assets/themes/admin/bower_components/datatables.net/js/jquery.dataTables.min.js');
                $this->output->js('assets/themes/admin/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js');
                $this->load->view('queue_listing', $data);
            }
            else{
                show_error('The Content you are trying to view does not exist.');
            }
            
        }else{
            show_error('The Content you are trying to view does not exist1.');
        }
    }

    function publish_queue(){
        // $data['queue_position']=$this->uri->segment(3);
        // $data['booking_person_name']=$this->uri->segment(4);
        // $data['ad_id']=$this->uri->segment(5);
        // $data['appo_date_time'] = $this->input->get('appo_date_time');

        $data['queue_position']=$this->input->post('queue_position');
        $data['booking_person_name']=$this->input->post('booking_person_names');
        $data['ad_id']=$this->input->post('ad_id');
        $data['appo_date_time'] = $this->input->post('appo_date_time');
        if($data['queue_position'] && $data['ad_id'] && $data['appo_date_time']){
            $queue=$this->Book->check_queue_exist($data['ad_id'],$data['appo_date_time']);
            
            if($queue){
                $params=array(
                    'booking_person_details'=>json_encode($data),
                    'appointment_date'=>$data['appo_date_time']
                );
                $this->Book->update_queue($params,$data['ad_id'],$data['appo_date_time']);
            }else{
                $params=array(
                    'booking_person_details'=>json_encode($data),
                    'ad_id'=> $data['ad_id'],
                    'appointment_date'=>$data['appo_date_time']
                );
                $this->Book->add_queue($params);                
            }      
        }else{
            show_error('The Content you are trying to view does not exist1.');
        }
    }

    function remove_queue(){

        $queue_position=$this->input->post('queue_position');
        $data['booking_person_name'] = $this->input->post('booking_person_names');
        $data['ad_id']=$this->input->post('ad_id');
        $data['appo_date_time'] = $this->input->post('appo_date_time');
        $booking_person_id=$this->input->post('booking_person_id');
        if($booking_person_id ){
            $queue=$this->Book->check_queue_exist($data['ad_id'],$data['appo_date_time']);
            $booking_person=json_decode($queue->booking_person_details)->booking_person_name;
            $loop_count=0;
            foreach($booking_person as $book_prsn){
                if($book_prsn->value==""){
                    $loop_count++;
                }else{
                    break;
                }
            }
                if($loop_count!=0){
                    $booking_person[$loop_count-1]->value="test_val";
                }
                
                $booking_person[$loop_count]->value="";
                $data['booking_person_name'] =$booking_person;
                // print_r($data['booking_person_name']);
                // die;
            
            // print_r($booking_person[$queue_position]->value);
            // unset($booking_person[$queue_position-1]);
            // print_r($booking_person);
            // die;
            $data['queue_position']=(json_decode($queue->booking_person_details)->queue_position)-1;
            
            
                $params=array(
                    'booking_person_details'=>json_encode($data),
                    'ad_id'=>$data['ad_id'],
                    'appointment_date'=>$data['appo_date_time']
                );
                $this->Book->update_queue($params,$data['ad_id'],$data['appo_date_time']);  

                $params1=array(
                    'client_confirmation_status'=>1
                );
                $this->Book->update_booking_person($params1,$booking_person_id); 

        }else{
            show_error('The Content you are trying to view does not exist.');
        }
    }

    function suscribe_guest() {

        if ( !$this->session->userdata('logged_in')) {
            redirect(base_url('users/auth'));
        }
        //For Listing
        if($this->session->userdata('logged_in')->user_role == 2){
            $this->output->section('header', 'welcome/header');
            $this->output->section('sidebar', 'welcome/sidebar');
            $this->output->section('footer', 'welcome/footer');
            $this->output->set_template('admin');
            $login_id=$this->session->userdata('logged_in')->id;
            $data['suscribes']=$this->Book->get_suscribe($login_id);
            $this->output->set_title('Guest Management');
            $this->output->css('assets/themes/admin/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css');
            $this->output->js('assets/themes/admin/bower_components/datatables.net/js/jquery.dataTables.min.js');
            $this->output->js('assets/themes/admin/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js');
            $this->load->view('suscribe_listing', $data);
        }else{
            show_error('The Content you are trying to view does not exist.');
        }
    }
    
    function complete_guest() {
        if ( !$this->session->userdata('logged_in')) {
            redirect(base_url('users/auth'));
        }
        //For Listing
        if($this->session->userdata('logged_in')->user_role == 2){
            $this->output->section('header', 'welcome/header');
            $this->output->section('sidebar', 'welcome/sidebar');
            $this->output->section('footer', 'welcome/footer');
            $this->output->set_template('admin');
            $login_id=$this->session->userdata('logged_in')->id;
            $data['guests_complete']=$this->Book->get_completed($login_id);
            $this->output->set_title('Guest Management');
            $this->output->css('assets/themes/admin/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css');
            $this->output->js('assets/themes/admin/bower_components/datatables.net/js/jquery.dataTables.min.js');
            $this->output->js('assets/themes/admin/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js');
            $this->load->view('completed_listing', $data);
        }else{
            show_error('The Content you are trying to view does not exist.');
        }
    }

    function update_guest_list(){
        $ids = $this->input->post('ids');
        $this->Book->update_guest_list($ids);
        echo"1";
        die;
    }

    function appointment() {        
        $client_id=$this->uri->segment(3);
        $ad_id=$this->uri->segment(4);
        $this->output->set_title('Book Appointment');
        $this->output->unset_template();
        $this->output->set_template('pages');

        if($client_id || $ad_id){      
            $data['secret']= $this->secret;
            $data['currency']= $this->currency;
            $data['merchant_id']= $this->merchant_id;
            $data['url']= $this->url;

            $data['ads']=$this->Book->get_add($ad_id);
            $data['client_id']= $client_id;
            $data['ad_id']= $ad_id;
            $this->load->view('add', $data);
            
        }else{
            show_error('Invalid Url');
        }
    }

    function get_date(){
        $client_id= $this->input->post('client_id');
        $ad_id=$this->input->post('ad_id');        
        $ads=$this->Book->fetch_add($client_id,$ad_id);

        $dates=json_decode($ads->appointment_date); 
       
        $dta=[];
        foreach($dates as $date){
            $s = strtotime($date);            
            //$dta[] = date('m/d/Y', $s);
            //$tem[] = date('H:i:s A', $s);
            if(!in_array(date('m/d/Y', $s), $dta)){
                $dta[] = date('m/d/Y', $s);
            }    
        }
        $data['dtas']=$dta;        
        $this->load->view('date_dropdown', $data);
    }
    
    function get_time(){
        $client_id= $this->input->post('client_id');
        $ad_id=$this->input->post('ad_id'); 
        $appo_date=$this->input->post('appo_date'); 

        $ads=$this->Book->fetch_add($client_id,$ad_id);
        $dates=json_decode($ads->appointment_date);        
        $tme=[];
        foreach($dates as $date){
            $s = strtotime($date);            
            $dt = date('m/d/Y', $s);
            if(strtotime($dt) == strtotime($appo_date)){
                $tme[] = date('h:i:s A', $s);
            }    
        }
        
        $data['tmes']=$tme;        
        $this->load->view('time_dropdown', $data);
    }

    function get_date_client(){
        $client_id= $this->input->post('client_id');
        $ad_id=$this->input->post('ad_id');        
        $ads=$this->Book->fetch_add($client_id,$ad_id);
        $dates=json_decode($ads->appointment_date);        
        $dta=[];
        foreach($dates as $date){
            $s = strtotime($date);           
            //$dta[] = date('m/d/Y', $s);
            //$tem[] = date('H:i:s A', $s);
            if(!in_array(date('m/d/Y', $s), $dta)){
                $dta[] = date('m/d/Y', $s);
            }    
        }
        $data['dtas']=$dta;        
        $this->load->view('date_dropdown_client', $data);
    }

    function get_time_client(){
        $client_id= $this->input->post('client_id');
        $ad_id=$this->input->post('ad_id'); 
        $appo_date=$this->input->post('appo_date'); 

        $ads=$this->Book->fetch_add($client_id,$ad_id);
        $dates=json_decode($ads->appointment_date);        
        $tme=[];
        foreach($dates as $date){
            $s = strtotime($date);            
            $dt = date('m/d/Y', $s);
            if(strtotime($dt) == strtotime($appo_date)){
                $tme[] = date('h:i:s A', $s);
            }    
        }
        
        $data['tmes']=$tme;        
        $this->load->view('time_dropdown_client', $data);
    }

    function add_queue(){
        $appo_date=$this->input->post('appo_date');
        $appo_time=$this->input->post('appo_time');
        $appo_date_time=$appo_date.' '.$appo_time;

        $ad_id= $this->input->post('ad_id');
        $booking_person_name= $this->input->post('name');
        $booking_person_email= $this->input->post('email');
        $admin_email=$this->db_config->admin_email;
        $email_sender_name=$this->db_config->email_sender_name;
        $email_send_text=$this->db_config->email_send_text;

        $params=array(
            'booking_person_name'=> $this->input->post('name'),
            'booking_person_email'=> $this->input->post('email'),
            'booking_person_number'=> $this->input->post('number'),     
            'appointment_date'=> $appo_date_time,                
            'user_id'=> $this->input->post('client_id'),
            'ad_id'=>$this->input->post('ad_id')
        );
        $ads_details=$this->Book->fetch_ads_details($ad_id);
        $advertiser_name=$ads_details->advertiser_name;
        // echo'<pre>';
        // print_r($ads_details);
        // die;
        $last_id=$this->Book->add_appointment_via_que($params);
        $last_id=$this->Book->mail($admin_email,$email_sender_name,$email_send_text,$ad_id, $appo_date,$appo_time, $appo_date_time, $last_id, $booking_person_email,$booking_person_name,$advertiser_name);
            echo "1";
            die;            
    }

    function queue_link(){
        $this->output->set_title('Enter Queue');
        $this->output->unset_template();
        $this->output->set_template('queue');
        $booking_person_id=$this->uri->segment(3);
        $ad_id=$this->uri->segment(4);   
        $data['appo_date_time'] = $this->input->get('appo_date_time');   

    
        if($booking_person_id && $ad_id && $data['appo_date_time']){            
            
            $fetch_booking_person_queue_pos=$this->Book->fetch_booking_person_queue_pos($ad_id,$data['appo_date_time']);
            $booking_person_count_pos=count($fetch_booking_person_queue_pos);            
            
            $booking_person_details=$this->Book->fetch_booking_person($ad_id,$data['appo_date_time']);
            $queue_detals=$this->Book->fetch_queue($ad_id,$data['appo_date_time']);

            $booking_person_count=count($booking_person_details);
            
            $queue_pos=1;
            foreach($booking_person_details as $booking_person_detail){
                if(($booking_person_detail->id) == $booking_person_id){
                    break;
                }else{
                    $queue_pos++;
                }
            }
            if($queue_pos<=$booking_person_count){
                $data['queue_pos']=$queue_pos;
            }else{
                $data['queue_pos']='';
            }

            if($queue_detals){
                $booking_persons_queue_positions=json_decode($queue_detals->booking_person_details)->booking_person_name;

                $value_positon_cnt=1;

                if($booking_persons_queue_positions){
                    $booking_persons_queue_positions_count=count($booking_persons_queue_positions);
                }                

                foreach($booking_persons_queue_positions as $booking_persons_queue_position){
                    if(!$booking_persons_queue_position->value){
                        $value_positon_cnt++;
                    }else{
                        break;
                    }
                }                   
                if($value_positon_cnt==$booking_persons_queue_positions_count+1){
                    if($booking_person_count_pos){
                        //echo "in innerif";
                        $data['value_present'] =1;
                    }else{
                        //echo "in innerelse";
                        $data['value_present'] ='';
                    }
                }else{
                    //echo "else";
                    $data['value_present'] = isset($booking_person_details[$value_positon_cnt]);
                }
                
            }

            $queues= $this->Book->check_queue_exist($ad_id,$data['appo_date_time']);            
            
            if($queues){
                $data['cnt']=json_decode($queues->booking_person_details)->queue_position;
            }else{
                $data['cnt']=0;
            }
            $data['booking_person_detail']=$this->Book->fetch_booking_person_details($booking_person_id);
            // echo'<pre>';
            // print_r($data['booking_person_detail']);
            // die;
            
            $data['ad_id']= $ad_id;
            $data['booking_person_id']=$booking_person_id;
            $this->load->view('queue', $data);
        }
        else{
            show_error('The Content you are trying to view does not exist.');
        }
    }
    public function cancel(){
         echo "  <script>
            if (confirm('Are You Sure?')){ ";
                 $id = $_GET['cid'];    
            $this->db->where("id",$id);
           $this->db->delete("booking_person");
           $this->load->helper('url');
       echo " window.location = 'https://easyhealthlink.livinglinks.city'; }else{ window.location = 'https://easyhealthlink.livinglinks.city'; }</script>";
         
        }

    function add_booking_via_payment(){
        $appo_date=$this->input->post('appo_date');
        $appo_time=$this->input->post('appo_time');
        $appo_date_time=$appo_date.' '.$appo_time;

        $params=array(
            'booking_person_name'=> $this->input->post('name'),
            'booking_person_email'=> $this->input->post('email'),
            'booking_person_number'=> $this->input->post('number'),     
            //'appointment_method'=> $appointment_method, 
            'appointment_date'=> $appo_date_time,                 
            'user_id'=> $this->input->post('client_id'),
            'ad_id'=>$this->input->post('ad_id')
        );
        $inserted_id=$this->Book->add_appointment_via_payment($params);
        echo $inserted_id;
        die;  
                  
    }

    function payment(){
        $ad_id=$this->uri->segment(3);
        $booking_id=$this->uri->segment(4);
        $client_id=$this->uri->segment(5);
        // Set variables for paypal form
        $returnURL = base_url().'books/paypal/success/'.$booking_id;
        $cancelURL = base_url().'books/paypal/cancel/'.$client_id.'/'.$ad_id;
        $notifyURL = base_url().'books/paypal/ipn/'.$booking_id;
        
        // Get product data from the database
        //$product = $this->Product->getRows($id);
        
        // Get current user ID from the session
        //print_r($this->session->userdata('logged_in')->id);
        $userID = $this->session->userdata('logged_in')->id;
        
        // Add fields to paypal form
        $this->paypal_lib->add_field('return', $returnURL);
        $this->paypal_lib->add_field('cancel_return', $cancelURL);
        $this->paypal_lib->add_field('notify_url', $notifyURL);
        //$this->paypal_lib->add_field('item_name', $product['name']);
        $this->paypal_lib->add_field('custom', $userID);
        $this->paypal_lib->add_field('ad_id',  $ad_id);
        $this->paypal_lib->add_field('booking_id',  $booking_id);
        
        // Render paypal form
        $this->paypal_lib->paypal_auto_form();
    }

    function payment_response(){ 

        $request=$_POST;  
        $appo_date=$request['appo_date'];
        $appo_time=$request['appo_time'];

        $appo_date_time=$appo_date.' '.$appo_time;
        echo"<div class='show_msg' style='background: #4ebd4e;;color: white;padding: 15px;text-align: center;width: 100%;border-radius: 5px;line-height: 26px;    margin-top: 45px;'>";  


        if($request['RESULT']=='00'){

            $ad_id= $request['ad_id'];
            $booking_person_name= $request['name'];
            $booking_person_email= $request['email'];
            $admin_email=$this->db_config->admin_email;
            $email_sender_name=$this->db_config->email_sender_name;
            $email_send_text=$this->db_config->email_send_text;

            $ads_details=$this->Book->fetch_ads_details($ad_id);
            $advertiser_name=$ads_details->advertiser_name;

            $params=array(
                'booking_person_name'=> $request['name'],
                'booking_person_email'=> $request['email'],
                'booking_person_number'=> $request['number'],        
                'appointment_date'=> $appo_date_time,                
                'user_id'=> $request['client_id'],
                'ad_id'=>$request['ad_id'],
                'appointment_method'=>1
            );
            $inserted_id=$this->Book->add_appointment_via_que($params);

            $this->Book->mail($admin_email,$email_sender_name,$email_send_text,$ad_id, $appo_date,$appo_time, $appo_date_time, $inserted_id, $booking_person_email,$booking_person_name,$advertiser_name);

            // $params=array(
            //     'booking_person_name'=> $request['name'],
            //     'booking_person_email'=> $request['email'],
            //     'booking_person_number'=> $request['number'],     
            //     //'appointment_method'=> $appointment_method, 
            //     'appointment_date'=> $appo_date_time,                 
            //     'user_id'=>$request['client_id'],
            //     'ad_id'=>$request['ad_id']
            // );
            // $inserted_id=$this->Book->add_appointment_via_payment($params);

            $payment_details=json_encode($request);
            $params_1=array(
                'payment_for'=> "guest_queue",
                'temp_booking_person_id'=> $inserted_id,
                'time_stamp'=> $request['TIMESTAMP'],
                'payment_gross'=> $request['AMOUNT'], 
                'currency_code'=> $request['BILLING_CO'],    
                'payer_email'=>$request['email'],
                'payment_status'=>$request['MESSAGE'],
                'order_id'=>$request['ORDER_ID'],
                'payment_details'=> $payment_details,               
                
            );
            $this->Book->add_payment($params_1);

            // $data['order_id']=$request['ORDER_ID'];
            // $data['ammount']=$request['AMOUNT'];
            // $data['email']=$request['email'];
            // $data['message']='Payment Has successfully completed';

            $ammount=$request['AMOUNT']/100;

            echo "Payment has Successfully Completed";
            echo'<br>';
            echo"Your Eamil is:".' '.$request['email'];
            echo'<br>';
            echo"Your Order Id is:".' '.$request['ORDER_ID'];
            echo'<br>';
            echo"Ammount is:".' '.$ammount.'('.$request['crncy'].')';
            echo'<br>';
            
        }elseif($request['RESULT']=='101'){
            echo "Declined by the bank";
            echo'<br>';
        }elseif($request['RESULT']=='102'){
            echo "Bank Requires more Information Before They can Approve the Transaction";
            echo'<br>';
        }
        elseif($request['RESULT']=='103'){
            echo "Card reported lost/stolen";
            echo'<br>';
        }
        else{
            echo "Communication Error";
            echo'<br>';
        }
        
        $url=base_url('books/appointment/').$request['client_id'].'/'.$request['ad_id'];        
        echo"</div>";
        echo"<a href='".$url."'>Back</a>";
        //$this->load->view('show_message');
        //redirect('books/show_payment_message/',$data);
    }

    function live_queue_payment_response(){ 

        $request=$_POST;  
        // echo'<pre>';
        // print_r($request);
        // die;
        // $appo_date=$request['appo_date'];
        // $appo_time=$request['appo_time'];

        // $appo_date_time=$appo_date.' '.$appo_time;
        echo"<div class='show_msg' style='background: #4ebd4e;;color: white;padding: 15px;text-align: center;width: 100%;border-radius: 5px;line-height: 26px;    margin-top: 45px;'>";  


        if($request['RESULT']=='00'){
         

            $ad_id= $request['ad_id'];
            $booking_person_name= $request['name'];
            $booking_person_email= $request['email'];
            $admin_email=$this->db_config->admin_email;
            $email_sender_name=$this->db_config->email_sender_name;
            $email_send_text=$this->db_config->email_send_text;

            $params=array(
                'booking_person_name'=> $this->input->post('name'),
                'booking_person_email'=> $this->input->post('email'),
                'booking_person_number'=> $this->input->post('number'),     
                'payment_status'=> 1,                
                'user_id'=> $this->input->post('client_id'),
                'ad_id'=>$this->input->post('ad_id')
            );
            $ads_details=$this->Book->fetch_ads_details($ad_id);
            $advertiser_name=$ads_details->advertiser_name;
            $last_id=$this->Book->add_live_queue($params);
            $this->Book->live_queue_mail($admin_email,$email_sender_name,$email_send_text,$ad_id, $last_id, $booking_person_name, $booking_person_email, $advertiser_name);


            $payment_details=json_encode($request);
            $params_1=array(
                'payment_for'=> "live_queue",
                'temp_booking_person_id'=> $last_id,
                'time_stamp'=> $request['TIMESTAMP'],
                'payment_gross'=> $request['AMOUNT'], 
                'currency_code'=> $request['BILLING_CO'],    
                'payer_email'=>$request['email'],
                'payment_status'=>$request['MESSAGE'],
                'order_id'=>$request['ORDER_ID'],
                'payment_details'=> $payment_details,            
                
            );
            $this->Book->add_payment($params_1);

            // $data['order_id']=$request['ORDER_ID'];
            // $data['ammount']=$request['AMOUNT'];
            // $data['email']=$request['email'];
            // $data['message']='Payment Has successfully completed';
            $ammount=$request['AMOUNT']/100;
            
            echo "Payment has Successfully Completed";
            echo'<br>';
            echo"Your Eamil is:".' '.$request['email'];
            echo'<br>';
            echo"Your Order Id is:".' '.$request['ORDER_ID'];
            echo'<br>';
            echo"Ammount is:".' '.$ammount.'('.$request['crncy'].')';
            echo'<br>';

            $url=base_url('books/live_queue_position/').$last_id.'/'.$request['ad_id'];        
            echo"</div>";
            //echo"<a href='".$url."'>Check Live Queue Position</a>";

            echo '<script type="text/javascript">setInterval(function(){ location.href = "'.$url.'" }, 5000);</script>';
            
        }elseif($request['RESULT']=='101'){
            echo "Declined by the bank";
            echo'<br>';
            $url=base_url('books/live_queue/').$request['client_id'].'/'.$request['ad_id'];        
            echo"</div>";
            echo"<a href='".$url."'>Back</a>";

        }elseif($request['RESULT']=='102'){
            echo "Bank Requires more Information Before They can Approve the Transaction";
            echo'<br>';
            $url=base_url('books/live_queue/').$request['client_id'].'/'.$request['ad_id'];        
            echo"</div>";
            echo"<a href='".$url."'>Back</a>";
        }
        elseif($request['RESULT']=='103'){
            echo "Card reported lost/stolen";
            echo'<br>';
            $url=base_url('books/live_queue/').$request['client_id'].'/'.$request['ad_id'];        
            echo"</div>";
            echo"<a href='".$url."'>Back</a>";
        }
        else{
            echo "Communication Error";
            echo'<br>';
            $url=base_url('books/live_queue/').$request['client_id'].'/'.$request['ad_id'];        
            echo"</div>";
            echo"<a href='".$url."'>Back</a>";
        }
        
        
        
        //$this->load->view('show_message');
        //redirect('books/show_payment_message/',$data);
    }


    function payment_complete(){
        
        $last_id=$this->input->post('last_id');        
        $temp_booking=$this->Book->fetch_temp_booking($last_id);
        $params=array(
            'booking_person_name'=> $temp_booking->booking_person_name,
            'booking_person_email'=> $temp_booking->booking_person_email,
            'booking_person_number'=> $temp_booking->booking_person_number,    
            'appointment_method'=> 1,                
            'user_id'=> $temp_booking->user_id, 
            'ad_id'=>$temp_booking->ad_id
        );
        $this->Book->add_appointment_via_que($params);      
        $this->Book->delete_temp_booking($last_id);   
        echo"1";    
        die;
    }

    function guest_listing(){
        if ( !$this->session->userdata('logged_in')) {
            redirect(base_url('users/auth'));
        }
        if($this->session->userdata('logged_in')->user_role == 1){

            $this->output->set_title('Booking Information');
            $this->output->section('header', 'welcome/header');
            $this->output->section('sidebar', 'welcome/sidebar');
            $this->output->section('footer', 'welcome/footer');
            $this->output->set_template('admin');

            $client_id=$this->uri->segment(3); 
            $data['queue_count']=$this->Book->get_guest_queue($client_id);   
            $data['suscribe_count']=$this->Book->get_guest_suscribe($client_id);  
            $data['complete_count']=$this->Book->get_guest_complete($client_id); 
            $data['user']=$this->Book->get_users($client_id); 
            $this->load->view('guest_listing', $data);
            
        }else{
            show_error('The Content you are trying to view does not exist.');
        }
    }

    function confirm_booking(){
        $booking_id=$this->uri->segment(3);  
        $queue_position=$this->uri->segment(4);
        $ad_id=$this->uri->segment(5);
        $appo_date_time = $this->input->get('appo_date_time');

        // echo'<pre>';
        // print_r($ad_id);
        // echo'<br>';
        // print_r($queue_position);
        // echo'<br>';
        // print_r($appo_date_time);
        // echo'<br>';
        // die;

        // $booking_person_details=$this->Book->fetch_queue($ad_id,$appo_date_time);
        // echo'<pre>';
        // print_r(json_decode($booking_person_details->booking_person_details));
        // die;
        $params=array(
            'queue_position'=> $queue_position,
            'queue_status'=> 1
        );
        $this->Book->update_booking_person($params,$booking_id); 
        // $booking_person_id=$this->uri->segment(3);
        // $ad_id=$this->uri->segment(4); 
        $appo_date_time = "appo_date_time=".$appo_date_time;
        redirect('books/queue_link/'.$booking_id.'/'.$ad_id.'?'.$appo_date_time);
    }

    function update_booking_person_status(){
        $booking_person_id=$this->input->post('booking_person_id');  
        $params=array(
            'client-confirmation_status'=> 1
        );
        $this->Book->update_booking_person_status($params,$booking_person_id); 
    }


    // $s = strtotime($booking_person_details->appointment_date);            
            // $booking_person_details_date = date('m/d/Y', $s);
            // $booking_person_details_time= date('h:i:s A', $s);
            
            // $cnt=0;
            // foreach($queues as $queue){
            //     $dt = strtotime($queue->appointment_date); 
                
            //     $booking_persons_details_date = date('m/d/Y', $dt);
            //     $booking_persons_details_time = date('h:i:s A', $dt);
            //     //echo $booking_persons_details_time;
            //     if((strtotime($booking_person_details_date) == strtotime($booking_persons_details_date))&&(strtotime($booking_person_details_time) == strtotime($booking_persons_details_time))){
            //         $cnt++;
            //     }
            // }
            // }else{
            //     $data['cnt']="You Have already Entered in queue";
            //     $data['status']=$booking_person_details->status;
            // }
            //die;
  
    public function mail(){
        $this->load->library('email');
        $this->email->set_mailtype("html");
         $ad_id=$this->input->post('ad_id');      
        $this->email->from('deepbn9@gmail.com', 'R.D.Barman');
        $this->db->select('user_id');
        $this->db->where('id', $ad_id);
        $q = $this->db->get('ads');
        $data = $q->result_array();
        $user_id = $data[0]['user_id'];
        $this->db->select('email');
        $this->db->where('id', $user_id);
        $q1 = $this->db->get('users');
        $data1 = $q1->result_array();
        $user_email = $data[0]['email'];
        $this->email->to($user_email);
        

        $this->email->subject('Email Test');
        $this->email->message('<h2>Appointment Confirmation:</h2>
      <p>  Hi, </p>
       <p> You have appointment with Registered Client Name.</p></br>
        
        <h3>When:</h3></br>
       <p> Date: Sunday, November 18 2018</p></b>
        <p>Time: 09:15 AM - 09:45 AM | Mountain Time (US & Canada)</p></br>
        
        <p><h3>How to join:</h3> <a href="http://easyhelp.livinglinks.city/">Click this Link</a> (Admin sets this guest link in registered client account)</p></br>
        <p><h3>Cancel The Appointment:</h3> <a href="http://easyhelp.livinglinks.city/book/cancel">Cancel The Appointment</a> (Admin sets this guest link in registered client account)</p></br>
        
       <p> <a href="http://easyhelp.livinglinks.city/"><< Add this to your google calendar >></a></p>
        ');

        $this->email->send();

    }


    function live_queue(){
        $client_id=$this->uri->segment(3);
        $ad_id=$this->uri->segment(4);
        $this->output->set_title('Enter Live Queue');
        $this->output->unset_template();
        $this->output->set_template('pages');

        if($client_id || $ad_id){      

            $data['secret']= $this->secret;
            $data['currency']= $this->currency;
            $data['merchant_id']= $this->merchant_id;
            $data['url']= $this->url;
            // echo'<pre>';
            // print_r($data);
            // die;
            $data['ads']=$this->Book->get_add($ad_id);
            $data['client_id']= $client_id;
            $data['ad_id']= $ad_id;
            $this->load->view('live_queue_add', $data);
            
        }else{
            show_error('Invalid Url');
        }
        // echo "enter";
        // die;
    }

    function add_live_queue(){
        // $appo_date=$this->input->post('appo_date');
        // $appo_time=$this->input->post('appo_time');
        // $appo_date_time=$appo_date.' '.$appo_time;

        $ad_id= $this->input->post('ad_id');
        $booking_person_name= $this->input->post('name');
        $booking_person_email= $this->input->post('email');
        $admin_email=$this->db_config->admin_email;
        $email_sender_name=$this->db_config->email_sender_name;
        $email_send_text=$this->db_config->email_send_text;

        $params=array(
            'booking_person_name'=> $this->input->post('name'),
            'booking_person_email'=> $this->input->post('email'),
            'booking_person_number'=> $this->input->post('number'),     
            //'appointment_date'=> $appo_date_time,                
            'user_id'=> $this->input->post('client_id'),
            'ad_id'=>$this->input->post('ad_id')
        );
        $ads_details=$this->Book->fetch_ads_details($ad_id);
        $advertiser_name=$ads_details->advertiser_name;
        $last_id=$this->Book->add_live_queue($params);
        $this->Book->live_queue_mail($admin_email,$email_sender_name,$email_send_text,$ad_id, $last_id, $booking_person_name, $booking_person_email, $advertiser_name);
        echo $last_id;
        die;            
    }

    function show_live_queue($queue=0) {
        if ( !$this->session->userdata('logged_in')) {
            redirect(base_url('users/auth'));
        }
        // echo "enter";
        // die;
        //For Listing
        if($this->session->userdata('logged_in')){
            $data['client_id']=$this->session->userdata('logged_in')->id;          
            $data['ad_id']=$this->uri->segment(3);
            if($data['ad_id']){

                $this->output->section('header', 'welcome/header');
                $this->output->section('sidebar', 'welcome/sidebar');
                $this->output->section('footer', 'welcome/footer');
                $this->output->set_template('admin');
                $login_id=$this->session->userdata('logged_in')->id;
                
                $data['live_queue_details']=$this->Book->fetch_live_queue($data['client_id'],$data['ad_id']);

                $data['ads']=$this->Book->get_add($data['ad_id']);
                //print_r($data['ads']);
                $data['weblink_1_text']=$this->db_config->weblink_1_text;
                $data['weblink_2_text']=$this->db_config->weblink_2_text;
                $data['weblink_3_text']=$this->db_config->weblink_3_text;
                // echo'<pre>';
                // print_r($data['weblink_1_text']);
                // echo'<br>';
                // print_r($data['weblink_2_text']);
                // echo'<br>';
                // print_r($data['weblink_3_text']);
                // echo'<br>';


                $data['show_hide_weblink_1']=$this->db_config->show_hide_weblink_1;
                $data['show_hide_weblink_2']=$this->db_config->show_hide_weblink_2;
                $data['show_hide_weblink_3']=$this->db_config->show_hide_weblink_3;

                // print_r($data['show_hide_weblink_1']);
                // echo'<br>';
                // print_r($data['show_hide_weblink_2']);
                // echo'<br>';
                // print_r($data['show_hide_weblink_3']);
                // echo'<br>';
                // echo'<pre>';
                // print_r($data['ads']);
                // die;
                // // echo'<pre>';
                // // print_r($data['live_queue_details']);
                // die;
                
                $this->output->set_title('Live Queue');
                $this->output->css('assets/themes/admin/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css');
                //$this->output->js('assets/themes/admin/bower_components/datatables.net/js/jquery.dataTables.min.js');
                $this->output->js('assets/themes/admin/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js');
                $this->load->view('live_queue_listing', $data);
            }
            else{
                show_error('The Content you are trying to view does not exist.');
            }
            
        }else{
            show_error('The Content you are trying to view does not exist.');
        }
    }

    function update_live_queue() {
        $data['ad_id']=$this->input->post('ad_id');
        $this->Book->toggle_ad_status($data['ad_id']);
    }


    function live_queue_position(){
        $this->output->set_title('Queue Position');
        $this->output->unset_template();
        $this->output->set_template('queue');
        $booking_id=$this->uri->segment(3);
        $ad_id=$this->uri->segment(4);
        // $appo_date=$this->input->post('appo_date');
        // $appo_time=$this->input->post('appo_time');
        // $appo_date_time=$appo_date.' '.$appo_time;
        if($booking_id && $ad_id ){

        
            $data['booking_person_status']=$this->Book->check_live_queue_booking_person_status($booking_id);
            $data['ads']=$this->Book->get_add($ad_id);
            
            
            $results=$this->Book->fetch_live_queue_position($booking_id,$ad_id);

            $queue_position=1;
            foreach($results as $result){
                if(($result->id)!=$booking_id){
                    $queue_position++;
                }else{
                    break;
                }
            }
            $data['queue_position']= $queue_position;

            $results_times=$this->Book->fetch_live_queues($booking_id,$ad_id);
            $loop_cnt=0;
            $total_time_count="00.00.00";
            foreach($results_times as $results_time){
                $loop_cnt++;
                $date_a = new DateTime($results_time->modified);
                $date_b = new DateTime($results_time->created);

                $interval = date_diff($date_a,$date_b);
                $time_diff=$interval->format('%h:%i:%s');

                $total_time_count = strtotime($time_diff) + strtotime($total_time_count) - strtotime('00:00:00');
                $total_time_count = date('H:i:s', $total_time_count);
            }
            // echo"here";
            // echo'<pre>';
            // print_r($total_time_count);
            // die;
            if($total_time_count!="00.00.00"){
                $time_array = explode(':', $total_time_count);
                $hours = (int)$time_array[0];
                $minutes = (int)$time_array[1];
                $seconds = (int)$time_array[2];
                $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                $average = floor($total_seconds / $loop_cnt);

                $seconds=$average; //the average time came in seconds

                $getHours = floor($seconds / 3600);
                $getMins = floor(($seconds - ($getHours*3600)) / 60);
                $getSecs = floor($seconds % 60);
                $avg_time_diff= $getHours.':'.$getMins.':'.$getSecs;

                $timestr = $avg_time_diff;
                $parts = explode(':', $timestr);
                $seconds = ($parts[0] * 60 * 60) + ($parts[1] * 60) + $parts[2];
                $total_second=$seconds*$queue_position;
                // echo"here";
                // echo'<pre>';
                // print_r($total_second);
                // die;
                $getHours = floor($total_second/ 3600);
                $getMins = floor(($total_second- ($getHours*3600)) / 60);
                $getSecs = floor($total_second% 60);
                // echo $getHours.':'.$getMins.':'.$getSecs;
                $ultimate_time= $getHours.':'.$getMins.':'.$getSecs;
                //echo $ultimate_time;
                $data['utimate_time']=$ultimate_time;
            }else{
                $data['utimate_time']="00:00:00";
            }

            $data['weblink_1_text']=$this->db_config->weblink_1_text;
            $data['weblink_2_text']=$this->db_config->weblink_2_text;
            $data['weblink_3_text']=$this->db_config->weblink_3_text;
            // echo'<pre>';
            // print_r($data['weblink_1_text']);
            // echo'<br>';
            // print_r($data['weblink_2_text']);
            // echo'<br>';
            // print_r($data['weblink_3_text']);
            // echo'<br>';


            $data['show_hide_weblink_1']=$this->db_config->show_hide_weblink_1;
            $data['show_hide_weblink_2']=$this->db_config->show_hide_weblink_2;
            $data['show_hide_weblink_3']=$this->db_config->show_hide_weblink_3;

            // print_r($data['show_hide_weblink_1']);
            // echo'<br>';
            // print_r($data['show_hide_weblink_2']);
            // echo'<br>';
            // print_r($data['show_hide_weblink_3']);
            // echo'<br>';
            // echo'<pre>';
            // print_r($ads);
            // die;
            // die;
            
            //die;
            $this->load->view('live_queue_position', $data);
        }else{
             show_error('The Content you are trying to view does not exist.');
        }
    }


    function update_live_queue_status() {
        $data['booking_person_id']=$this->input->post('booking_person_id');
        $this->Book->update_live_queue_status($data['booking_person_id']);
    }

}


