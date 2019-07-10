<?php (defined('BASEPATH')) OR exit('No direct script access allowed');


class Ads extends MY_Controller {

    function __construct() {
        parent::__construct();

        if ( !$this->session->userdata('logged_in')) {
            redirect(base_url('users/auth'));
        }

        $this->load->model('ad');
        $this->load->model('categories/category');
        $this->output->section('header', 'welcome/header');
        $this->output->section('sidebar', 'welcome/sidebar');
        $this->output->section('footer', 'welcome/footer');
        $this->output->set_template('admin');

        $query=$this->db->get('site_config')->result();
        $this->db_config=new stdClass();

        foreach ($query as $conf) {
            $key=$conf->config_name;
            $this->db_config->$key=$conf->value;
        }
    }

    function index() {
        //For Listing
        $data['ads']=$this->ad->get_ads();
        $this->output->set_title('ad Management');
        $this->output->css('assets/themes/admin/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css');
        $this->output->js('assets/themes/admin/bower_components/datatables.net/js/jquery.dataTables.min.js');
        $this->output->js('assets/themes/admin/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js');
        $this->load->view('listing', $data);
    }

    function add($client_id=null) {
        $this->output->js('https://cdn.ckeditor.com/4.11.1/standard/ckeditor.js');

        $video_size=$this->db_config->video_size;
        $data['video_size']=$video_size;

        $this->output->set_title('New ad');
        $data['category_names']=$this->category->get_category_names();
        $data['hide_price']=false;
        $data['client_id']=$client_id?$client_id: '';

        if($this->db_config->hide_ad_price && $this->session->userdata('logged_in')->user_role !=1) {
            $data['hide_price']=true;
        }

        $data['site_filter_option1_label']=$this->db_config->site_filter_option1_label;
        $data['site_filter_option2_label']=$this->db_config->site_filter_option2_label;
        $data['weblink_1']=$this->db_config->show_hide_weblink_1;
        $data['weblink_2']=$this->db_config->show_hide_weblink_2;
        $data['weblink_3']=$this->db_config->show_hide_weblink_3;
        $data['weblink_1_text']=$this->db_config->weblink_1_text;
        $data['weblink_2_text']=$this->db_config->weblink_2_text;
        $data['weblink_3_text']=$this->db_config->weblink_3_text;

        // echo'<pre>';
        // print_r($data['weblink_1']);
        // print_r($data['weblink_2']);
        // print_r($data['weblink_3']);
        // die;


        if($this->ad->verify_validation()) {


            $video=$this->ad->upload_video('video', 'videos', $video_size);

            if(($video['status']=='success')|| ( !$_FILES['video']['name'])) {

                if( !$_FILES['video']['name']) {
                    // echo"in if";
                    // die;
                    $data1['video']='';
                }

                else {
                    // echo"in else";
                    // die;
                    $data1['video']=$video['file_name'];
                }

                $image_upload=$this->ad->multiple_image_upload('userfile');

                if($this->input->post('client_id')) {
                    $id=$this->input->post('client_id');
                }

                else {
                    $id=$this->session->userdata('logged_in')->id;
                }


                $params=array('status'=> $this->input->post('status'),
                'choose'=> $this->input->post('choose'),
                'description'=> $this->input->post('description'),
                'advertiser_name'=>$this->input->post('advertiser_name'),
                'advertiser_phone'=>$this->input->post('advertiser_phone'),
                'lat'=>$this->input->post('lat'),
                'lng'=>$this->input->post('lng'),
                'adtitle'=>$this->input->post('adtitle'),
                'price'=>$this->input->post('price'),
                'my_address'=>$this->input->post('my_address'),
                'web_link_1'=>$this->input->post('web_link_1'),
                'web_link_2'=>$this->input->post('web_link_2'),
                'web_link_3'=>$this->input->post('web_link_3'),
                'merchant_name'=>$this->input->post('merchant_name'),
                'merchant_phone'=>$this->input->post('merchant_phone'),
                'show_hide_web_link'=>$this->input->post('web_link'),
                'merchant_address'=>$this->input->post('merchant_address'),
                'category_id'=> $this->input->post('category_id'),
                'fax'=> $this->input->post('fax'),
                'video'=> $data1['video'],
                'user_id'=>$id);


                //print_r($params);die;

                $this->ad->add_ad($params);
                $last_id=$this->db->insert_id();

                if($image_upload) {
                    $image=$image_upload['file_name'];
                }

                else {
                    $image='';
                }

                $params1=array('ad_image'=> $image,
                'ad_id'=> $last_id);
                $this->ad->add_ad_img($params1);




                if($this->input->post('client_id')) {
                    redirect('users/adds_listing?client_id='.$this->input->post('client_id'));
                }

                else {
                    redirect('ads/index');
                }
            }

            else {
                $data['video_error']="The file you are attempting to upload is larger than the permitted size.";

                $this->load->view('add', $data);

                //die;
            }

        }

        else {
            //Show Add Page


            $this->load->view('add', $data);
        }
    }


    function edit($id=0) {

        $this->output->js('https://cdn.ckeditor.com/4.11.1/standard/ckeditor.js');
        // check if the row exists before trying to edit it
        $data['ad']=$this->ad->get_ad($id);
        //print_r($data['ad']);die;
        $cid=$data['ad']->id;
        //print_r($cid);die;
        $data['adimages']=$this->ad->fetch_ad_images($cid);

        $video_size=$this->db_config->video_size;
        $data['video_size']=$video_size;

        //print_r($data['adimages']);die;
        if(isset($data['ad']->id)) {

            if($this->ad->verify_validation()) {


                if($_FILES['video']['name']) {

                    $video=$this->ad->upload_video('video', 'videos', $video_size);

                    if($video['status']=='success') {
                        $data1['video']=$video['file_name'];
                    }

                    else {

                        $this->session->set_flashdata('message_name', 'The file you are attempting to upload is larger than the permitted size.');
                        redirect('ads/edit/'.$cid);
                    }

                }

                else {
                    $old_video=$this->input->post('old_video');
                    if($old_video){
                        $data1['video']=$old_video;
                    }else{
                        $data1['video']='';
                    }                    
                }




                $client_id=$this->input->post('client_id');


                $image_upload=$this->ad->multiple_image_upload('userfile');

                $params=array('status'=> $this->input->post('status'),
                'choose'=> $this->input->post('choose'),
                'description'=> $this->input->post('description'),
                'address'=>$this->input->post('address'),
                'region'=>$this->input->post('region'),
                'city'=>$this->input->post('city'),
                'state'=>$this->input->post('state'),
                'country'=>$this->input->post('country'),
                'postal_code'=>$this->input->post('postal_code'),
                'lat'=>$this->input->post('lat'),
                'lng'=>$this->input->post('lng'),
                'adtitle'=>$this->input->post('adtitle'),
                'price'=>$this->input->post('price'),
                'my_address'=>$this->input->post('my_address'),
                'web_link_1'=>$this->input->post('web_link_1'),
                'web_link_2'=>$this->input->post('web_link_2'),
                'web_link_3'=>$this->input->post('web_link_3'),
                'merchant_name'=>$this->input->post('merchant_name'),
                'merchant_phone'=>$this->input->post('merchant_phone'),
                'advertiser_name'=>$this->input->post('advertiser_name'),
                'advertiser_phone'=>$this->input->post('advertiser_phone'),
                'show_hide_web_link'=>$this->input->post('web_link'),
                //'merchant_id'=>$this->input->post('merchant_id'),
                //'image' => $data1['image'],
                'video'=> $data1['video'],
                'category_id'=> $this->input->post('category_id'),
                'fax'=> $this->input->post('fax'),
                'merchant_address'=>$this->input->post('merchant_address'));
                //print_r($params);die;
                $this->ad->update_ad($id, $params);
                $image_values=$this->input->post('image_values');

                //print_r($image_values);die;
                if( !$image_upload['file_name']) {
                    $image_values=$image_values;
                }

                elseif( !$image_values) {
                    $image_values=$image_upload['file_name'];
                }

                else {
                    $image_values=$image_upload['file_name'].'#'.$image_values;
                }

                // print_r($image_values);
                // print_r($image_upload['file_name']);
                // print_r($images_values);

                // die;

                $params1=array('ad_image'=> $image_values);
                $this->ad->update_ad_img($params1, $cid);

                if($this->session->userdata('logged_in')->user_role==1) {
                    //echo $client_id;die;
                    redirect('users/adds_listing?client_id='.$client_id);
                }

                else {
                    redirect('ads/index');
                }
            }

            else {

                //Show Edit Page
                $data['cid']=$cid;
                $this->output->set_title('Edit ad');
                $data['category_names']=$this->category->get_category_names();
                $data['hide_price']=false;

                if($this->db_config->hide_ad_price && $this->session->userdata('logged_in')->user_role !=1) {
                    $data['hide_price']=true;
                }
                $data['weblink_1']=$this->db_config->show_hide_weblink_1;
                $data['weblink_2']=$this->db_config->show_hide_weblink_2;
                $data['weblink_3']=$this->db_config->show_hide_weblink_3;
                $data['weblink_1_text']=$this->db_config->weblink_1_text;
                $data['weblink_2_text']=$this->db_config->weblink_2_text;
                $data['weblink_3_text']=$this->db_config->weblink_3_text;

                //print_r($data);die;
                $data['site_filter_option1_label']=$this->db_config->site_filter_option1_label;
                $data['site_filter_option2_label']=$this->db_config->site_filter_option2_label;

                $this->load->view('edit', $data);
            }
        }

        else {
            show_error('The Content you are trying to edit does not exist.');
        }
    }

    function delete_images() {
        $image_value=$this->input->post('image_value');
        $image_values=$this->input->post('image_values');
        $cid=$this->input->post('cid');
        $image_values=(explode("#", $image_values));
        $images=array_diff($image_values, array($image_value));
        $images=implode("#", $images);
        $params1=array('ad_image'=> $images);
        $this->ad->update_ad_img($params1, $cid);

        echo $images;
        die;


    }

    function delete($id=0) {
        $data['ad']=$this->ad->get_ad($id);

        // check if the user exists before trying to delete it
        if(isset($data['ad']->id)) {
            $this->ad->delete_ad($id);
            redirect('ads/index');
        }

        else {
            show_error('The content you are trying to delete does not exist.');
        }
    }

    function togglestatus($id=0) {
        $data['ad']=$this->ad->get_ad($id);

        if(isset($data['ad']->id)) {
            $this->ad->toggle_status($id);
            redirect('ads/index');
        }

        else {
            show_error('The content you are trying to delete does not exist.');
        }

    }

}