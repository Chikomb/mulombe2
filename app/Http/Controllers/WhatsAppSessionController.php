<?php

namespace App\Http\Controllers;

use App\Models\DataCategory;
use App\Models\DataSurvey;
use App\Models\Language;
use App\Models\WhatsAppSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;



class WhatsAppSessionController extends Controller
{
    function generateSessionId() {
        $prefix = 'WA'; // Prefix for the account number
        $suffix = time(); // Suffix for the account number (UNIX timestamp)

        // Generate a random number between 1000 and 9999
        $random = rand(100000000000, 999999999999);

        // Combine the prefix, random number, and suffix to form the account number
        $payment_reference_number = $prefix . $random;

        return $payment_reference_number;
    }

    public function WhatsApp_Verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        // Check the mode and token sent are correct
        if ($mode == "subscribe" && $token == "mapalo") {
            // Respond with 200 OK and challenge token from the request
            echo $challenge;
           // http_response_code(200);
        } else {
            // Responds with '403 Forbidden' if verify tokens do not match
            return response()->json("Fobidden", 403);
        }

    }

    public function WhatsApp_Bot(Request $request)
    {
        Log::info('From WhatsApp', ['request' => $request->json()]);

        $from = "";
        $user_message = "";
        $phone_number = "";

        if (array_key_exists('messages', $request->json()['entry'][0]['changes'][0]['value'])) {
            $from = $request['entry'][0]['changes'][0]['value']['messages'][0]['from'];
            $user_message = $request['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
            $phone_number = $request['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];

            $language = 1;
            $case_no = 1;
            $step_no = 0;
            $message_string = "";

            $session_id = $this->generateSessionId();
            $data_category = DataCategory::where('is_active', 1)->first()->name;
            $telecom_operator = "Unknown";
            if(str_starts_with($phone_number, '096') || str_starts_with($phone_number, '26096') || str_starts_with($phone_number, '076') || str_starts_with($phone_number, '26076'))
            {
                $telecom_operator = "MTN";
            }elseif(str_starts_with($phone_number, '095') || str_starts_with($phone_number, '26095') || str_starts_with($phone_number, '075') || str_starts_with($phone_number, '26075')){
                $telecom_operator = "Zamtel";
            }elseif(str_starts_with($phone_number, '097') || str_starts_with($phone_number, '26097') || str_starts_with($phone_number, '077') || str_starts_with($phone_number, '26077')) {
                $telecom_operator = "Airtel";
            }

            //getting last session info
            $getLastSessionInfor = WhatsAppSession::where('phone_number', $from)->where('status', 0)->orderBy('id', 'DESC')->first();

            //checking if there is an active session or not
            if(!empty($getLastSessionInfor)){
                $case_no = $getLastSessionInfor->case_no;
                $step_no = $getLastSessionInfor->step_no;
                $session_id = $getLastSessionInfor->session_id;

                if($case_no == 1 && $step_no == 1 && !empty($user_message))
                {
                    $language = $user_message;
                    //update the session details
                    $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                        "language" => $user_message
                    ]);

                }else {
                    $language = $getLastSessionInfor->language;
                }
            }else{
                //save new session record
                $new_session = WhatsAppSession::create([
                    "phone_number" => $from,
                    "case_no" => 1,
                    "step_no" => 0,
                    "session_id" => $session_id,
                    "language_id" => $language
                ]);
                $new_session->save();
            }

            switch ($case_no) {
                case '1':
                    if($case_no == 1 && $step_no == 0){
                        $geLanguages = Language::where('is_active', 1)->get();

                        $language_menu = "Akros and Ministry of health are conducting a survey. Choose language";

                        $lists = $geLanguages;
                        $counter = 1;

                        foreach ($lists as $list) {
                            $language_menu = $language_menu . "\n" . $counter . ". " . $list->name;
                            $product_list[$counter] = $list->id;
                            $counter = 1 + $counter;
                        }

                        $message_string = $language_menu;

                        $imageURL = "https://res.cloudinary.com/kwachapay/image/upload/v1685364563/Akros_web-dark_tso3mu.png";

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 1,
                            "step_no" => 1
                        ]);

                        return $this->sendImageMessage($message_string,$phone_number, $from, $imageURL);

                    }elseif($case_no == 1 && $step_no == 1 && !empty($user_message)){

                        if($language == 1) //english
                        {
                            $message_string = "AKROS and Ministry of health are conducting a survey. If you are 18 years or older and wish to proceed?. \n\n1. Yes \n2. No";
                        }elseif($language == 2) //nyanja
                        {
                            $message_string = "AKROS ndi Unduna wa Zaumoyo akuchita kafukufuku. Ngati muli ndi zaka khumi ndi zisanu ndi zitatu kapena kuposerapo ndipo mukufuna kupitiriza? . \n\n1. Inde \n2. Ayi";
                        }elseif($language == 3) //bemba
                        {
                            $message_string = "AKROS na Ministry of Health balifye uma ukulandafye umutende. Ngabakwata umwaka umo na fwela, nafimbi ukupeza ifikolwe? \n\n1. Endita mukwai \n2. Iyo mukwai";
                        }elseif($language == 4) //tonga
                        {
                            $message_string = "Ai AKROS na Ministry of Health 'a kufutisa insala. Bula kuukata tinebo kumalukula 18 uku mukufyala kukukolokoti? \n\n1. Ee \n2. Awe";
                        }elseif($language == 5) //lozi
                        {
                            $message_string = "AKROS na Ministry of Health baakwiyanisa katundu. Lutango lwasi-18 ahebo lunzima ku kuendela? \n\n1. Ena \n2. Hae";
                        }elseif($language == 6) //lunda
                        {
                            $message_string = "AKROS na Ministry ya Musokolwa ishasha utusokolwa. Nkashi lwandi watau masumu ya mundu kumweni kumukasanga? \n\n1. Ehe \n2. Hae"
                            ;                    }elseif($language == 7) //luvale
                        {
                            $message_string = "AKROS na Mutundu wa Mbeu aveba shingwana shikongomelo. Uta landa vata ka mavilu kumabili na mwikaji, elacitandale? \n\n1. Eyo \n2. Teya";
                        }elseif($language == 8) //kaonde
                        {
                            $message_string = "AKROS na Ministeri ya bulamfula abaaka fiyamba ifiabo. Ukatembula mwiiko ichitatu munakate, efwabuka? \n\n1. Eyo \n2. Teya";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 1,
                            "step_no" => 2
                        ]);

                        return $this->sendMessage($message_string,$phone_number, $from);

                    }elseif($case_no==1 && $step_no == 2 && !empty($user_message))
                    {
                        if($user_message == "1")// register account
                        {
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp",
                                "question_number" => "1",
                                "question" => "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2.",
                                "answer" => "1",
                                "answer_value" => "Yes",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $message_string = "What is your age? (Enter in years)";

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 1
                            ]);

                            return $this->sendMessage($message_string,$phone_number, $from);

                        }elseif($user_message == "2") //Learn More
                        {
                            $message_string = "Thank you for your input, have a nice day";

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp",
                                "question_number" => "1",
                                "question" => "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2.",
                                "answer" => "2",
                                "answer_value" => "No",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $save_user = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp"
                            ]);

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 1,
                                "status" => 1
                            ]);

                            return $this->sendMessage($message_string,$phone_number, $from);

                        }else{
                            $message_string = "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2. \n\n1. Yes \n2. No";
                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 1
                            ]);

                            return $this->sendMessage($message_string,$phone_number, $from);
                        }
                    }
                    break;
                case '2':
                    if ($case_no == 2 && $step_no == 1 && !empty($user_message))
                    {
                        $save_data = DataSurvey::create([
                            "session_id" => $session_id,
                            "phone_number" => $from,
                            "channel" => "WhatsApp",
                            "question_number" => "2",
                            "question" => "What is your age? (Enter in years)",
                            "answer" => $user_message,
                            "answer_value" => $user_message,
                            "telecom_operator" => $telecom_operator,
                            "data_category" => $data_category
                        ]);

                        $save_data->save();

                        $message_string = "What is your gender \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 2,
                            "step_no" => 2
                        ]);

                        return $this->sendMessage($message_string,$phone_number, $from);

                    }elseif($case_no == 2 && $step_no == 2 && !empty($user_message)){
                        $gender = "Male";
                        if($user_message == "1"){
                            $gender = "Male";
                        }elseif($user_message == "2"){
                            $gender = "Female";
                        }elseif($user_message == "3"){
                            $gender = "Other";
                        }elseif($user_message == "2"){
                            $gender = "Prefer not to say";
                        }else{
                            $gender = "invalid input";
                        }

                        $save_data = DataSurvey::create([
                            "session_id" => $session_id,
                            "phone_number" => $from,
                            "channel" => "WhatsApp",
                            "question_number" => "3",
                            "question" => "What is your gender?",
                            "answer" => $user_message,
                            "answer_value" => $gender,
                            "telecom_operator" => $telecom_operator,
                            "data_category" => $data_category
                        ]);

                        $save_data->save();

                        $message_string = "In which District do you live? \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 2,
                            "step_no" => 3
                        ]);

                        return $this->sendMessage($message_string,$phone_number, $from);

                    }elseif($case_no == 2 && $step_no == 3 && !empty($user_message))
                    {
                        if($user_message == "1"){
                            //save the Lusaka district
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp",
                                "question_number" => "4",
                                "question" => "In which District do you live?",
                                "answer" => "1",
                                "answer_value" => "Lusaka",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $message_string = "Which constituency do you live in? \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 4
                            ]);

                            return $this->sendMessage($message_string,$phone_number, $from);

                        }elseif($user_message == "2"){
                            //Kalomo District


                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp",
                                "question_number" => "1",
                                "question" => "In which District do you live?",
                                "answer" => "4",
                                "answer_value" => "Lusaka",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $message_string = "Which constituency do you live in? \n1. Dundumwezi \n2. Kalomo Central";

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 5
                            ]);

                            return $this->sendMessage($message_string,$phone_number, $from);

                        }elseif($user_message == "3"){
                            //chavuma District

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp",
                                "question_number" => "4",
                                "question" => "In which District do you live?",
                                "answer" => "1",
                                "answer_value" => "Lusaka",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $message_string = "Which constituency do you live in? \n1. Chavuma";

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 6
                            ]);

                            return $this->sendMessage($message_string,$phone_number, $from);

                        }else{
                            $message_string = "You have selected an invalid option. \n1. Go Back";
                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 3
                            ]);
                            return $this->sendMessage($message_string,$phone_number, $from);
                        }



                    }elseif($case_no == 2 && $step_no == 4 && !empty($user_message)){
                        if($user_message == "1"){
                            //Chawama Constituency
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "Which constituency do you live in?",
                                "answer" => "1",
                                "answer_value" => "Chawama",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $message_string = "In which Ward do you stay in? \n1. ";
                        }
                    }
                    break;
            }
        }else{
            Log::info('WhatsApp Error', ['no message' => json_encode($request)]);
        }



    }

    function sendMessage($message_string, $phone_number, $send_to)
    {
        $token = env('WHATSAPP_TOKEN');

        $send_message = Http::withHeaders([
            'headers' => ['Content-Type' => 'application/json']
        ])->post('https://graph.facebook.com/v12.0/' . $phone_number . '/messages?access_token='.$token, [
            'messaging_product' => 'whatsapp',
            'to' => $send_to,
            'text' => ['body' => $message_string],
        ]);

        $responseBody = $send_message->body();
        Log::info('Send Message', ['response' => $responseBody]);

        return response('success',200);
    }

    function sendImageMessage($message_string, $phone_number, $send_to, $imageURL)
    {
        $token = env('WHATSAPP_TOKEN');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $send_to,
            'attachment' => [
                'type' => 'image',
                'payload' => [
                    'url' => $imageURL,
                    'caption' => $message_string
                ]
            ],
            'text' => ['body' => $message_string],
        ];

        $send_image_message = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post('https://graph.facebook.com/v12.0/' . $phone_number . '/messages', $payload);

        $responseBody = $send_image_message->body();
        Log::info('Send Message', ['response' => $responseBody]);

        return response('success', 200);
    }


}
