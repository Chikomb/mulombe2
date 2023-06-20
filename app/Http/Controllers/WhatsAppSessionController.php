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
    function generateSessionId()
    {
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

        $from = "";
        $user_message = "";
        $phone_number = "";

        if ($request) {
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
            if (str_starts_with($phone_number, '096') || str_starts_with($phone_number, '26096') || str_starts_with($phone_number, '076') || str_starts_with($phone_number, '26076')) {
                $telecom_operator = "MTN";
            } elseif (str_starts_with($phone_number, '095') || str_starts_with($phone_number, '26095') || str_starts_with($phone_number, '075') || str_starts_with($phone_number, '26075')) {
                $telecom_operator = "Zamtel";
            } elseif (str_starts_with($phone_number, '097') || str_starts_with($phone_number, '26097') || str_starts_with($phone_number, '077') || str_starts_with($phone_number, '26077')) {
                $telecom_operator = "Airtel";
            }

            //getting last session info
            $getLastSessionInfor = WhatsAppSession::where('phone_number', $from)->where('status', 0)->orderBy('id', 'DESC')->first();

            //checking if there is an active session or not
            if (!empty($getLastSessionInfor)) {
                $case_no = $getLastSessionInfor->case_no;
                $step_no = $getLastSessionInfor->step_no;
                $session_id = $getLastSessionInfor->session_id;
                $language = $getLastSessionInfor->language_id;

                if ($case_no == 1 && $step_no == 1 && !empty($user_message)) {
                    $language = $user_message;
                    //update the session details
                    $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                        "language_id" => $user_message
                    ]);

                }

            } else {
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
                    if ($case_no == 1 && $step_no == 0) {
                        $geLanguages = Language::where('is_active', 1)->get();

                        $language_menu = "*Akros and Ministry of health are conducting a survey. Choose language*\n";

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

                        $this->sendImageMessage($phone_number, $from, $imageURL);

                        return $this->sendMessage($message_string, $phone_number, $from);

                    } elseif ($case_no == 1 && $step_no == 1 && !empty($user_message)) {

                        $chosen_language = "English";

                        if ($language == 1) //english
                        {
                            $message_string = "*AKROS and Ministry of health are conducting a survey. If you are 18 years or older and wish to proceed?.* \n\n1. Yes \n2. No";
                        } elseif ($language == 2) //nyanja
                        {
                            $message_string = "*AKROS ndi Unduna wa Zaumoyo akuchita kafukufuku. Ngati muli ndi zaka khumi ndi zisanu ndi zitatu kapena kuposerapo ndipo mukufuna kupitiriza?.* \n\n1. Inde \n2. Ayi";
                            $chosen_language = "Nyanja";
                        } elseif ($language == 3) //bemba
                        {
                            $message_string = "*AKROS na Ministry of Health balifye uma ukulandafye umutende. Ngabakwata umwaka umo na fwela, nafimbi ukupeza ifikolwe?* \n\n1. Endita mukwai \n2. Iyo mukwai";
                            $chosen_language = "Bemba";
                        } elseif ($language == 4) //tonga
                        {
                            $message_string = "*Ai AKROS na Ministry of Health 'a kufutisa insala. Bula kuukata tinebo kumalukula 18 uku mukufyala kukukolokoti?* \n\n1. Ee \n2. Awe";
                            $chosen_language = "Tonga";
                        } elseif ($language == 5) //lozi
                        {
                            $message_string = "*Akros niba liluko la makete (Ministry of Health) basweli kueza patisiso kuamana nibutata bobutisizwe ki butuku bwa Covid 19 kwa sicaba mwa naha Zambia. Haiba munani ni lilimo ze 18 kuya fahalimu mi mubata kuzwela pili,* \n\n1. Eni \n2. Batili";
                            $chosen_language = "Lozi";
                        } elseif ($language == 6) //lunda
                        {
                            $message_string = "*AKROS na Ministry ya Musokolwa ishasha utusokolwa. Nkashi lwandi watau masumu ya mundu kumweni kumukasanga?* \n\n1. Ehe \n2. Hae";
                            $chosen_language = "Lunda";
                        } elseif ($language == 7) //luvale
                        {
                            $message_string = "*AKROS na Mutundu wa Mbeu aveba shingwana shikongomelo. Uta landa vata ka mavilu kumabili na mwikaji, elacitandale?* \n\n1. Eyo \n2. Teya";
                            $chosen_language = "Luvale";
                        } elseif ($language == 8) //kaonde
                        {
                            $message_string = "*AKROS na Ministeri ya bulamfula abaaka fiyamba ifiabo. Ukatembula mwiiko ichitatu munakate, efwabuka?* \n\n1. Eyo \n2. Teya";
                            $chosen_language = "Kaonde";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 1,
                            "step_no" => 2
                        ]);

                        $ticked_language = $chosen_language." ✅ ";
                        $selected_language = $this->sendMessage($ticked_language, $phone_number, $from);
                        return $this->sendMessage($message_string, $phone_number, $from);

                    } elseif ($case_no == 1 && $step_no == 2 && !empty($user_message)) {
                        if($user_message == "1" || $user_message == 1)// register account
                        {
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "1",
                                "question" => "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2.",
                                "answer" => "1",
                                "answer_value" => "Yes",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "What is your age? (Enter in years)";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "What is your age? (Enter in years)";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "What is your age? (Enter in years)";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "What is your age? (Enter in years)";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "Munani lilimo zekai (mun’ole lilimo) \n\n";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "What is your age? (Enter in years)";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "What is your age? (Enter in years)";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "What is your age? (Enter in years)";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 1
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        } elseif ($user_message == "2" || $user_message == 2) //Learn More
                        {

                            if ($language == 1) //english
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "Thank you for your input, have a nice day";
                            }

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "1",
                                "question" => "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2.",
                                "answer" => "2",
                                "answer_value" => "No",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 1,
                                "status" => 1
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        } else {
                            if ($language == 1) //english
                            {
                                $message_string = "*AKROS and Ministry of health are conducting a survey. If you are 18 years or older and wish to proceed?.* \n\n1. Yes \n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*AKROS ndi Unduna wa Zaumoyo akuchita kafukufuku. Ngati muli ndi zaka khumi ndi zisanu ndi zitatu kapena kuposerapo ndipo mukufuna kupitiriza?.* \n\n1. Inde \n2. Ayi";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*AKROS na Ministry of Health balifye uma ukulandafye umutende. Ngabakwata umwaka umo na fwela, nafimbi ukupeza ifikolwe?* \n\n1. Endita mukwai \n2. Iyo mukwai";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Ai AKROS na Ministry of Health 'a kufutisa insala. Bula kuukata tinebo kumalukula 18 uku mukufyala kukukolokoti?* \n\n1. Ee \n2. Awe";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*Akros niba liluko la makete (Ministry of Health) basweli kueza patisiso kuamana nibutata bobutisizwe ki butuku bwa Covid 19 kwa sicaba mwa naha Zambia. Haiba munani ni lilimo ze 18 kuya fahalimu mi mubata kuzwela pili,* \n\n1. Eni \n2. Batili";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*AKROS na Ministry ya Musokolwa ishasha utusokolwa. Nkashi lwandi watau masumu ya mundu kumweni kumukasanga?* \n\n1. Ehe \n2. Hae";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*AKROS na Mutundu wa Mbeu aveba shingwana shikongomelo. Uta landa vata ka mavilu kumabili na mwikaji, elacitandale?* \n\n1. Eyo \n2. Teya";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*AKROS na Ministeri ya bulamfula abaaka fiyamba ifiabo. Ukatembula mwiiko ichitatu munakate, efwabuka?* \n\n1. Eyo \n2. Teya";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 1
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }

                    }
                    break;
                case '2':
                    if ($case_no == 2 && $step_no == 1 && !empty($user_message)) {
                        $save_data = DataSurvey::create([
                            "session_id" => $session_id,
                            "phone_number" => $from,
                            "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                            "channel" => "WhatsApp",
                            "question_number" => "2",
                            "question" => "What is your age? (Enter in years)",
                            "answer" => $user_message,
                            "answer_value" => $user_message,
                            "telecom_operator" => $telecom_operator,
                            "data_category" => $data_category
                        ]);

                        $save_data->save();

                        if ($language == 1) //english
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 2) //nyanja
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 3) //bemba
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 4) //tonga
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 5) //lozi
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 6) //lunda
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 7) //luvale
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        } elseif ($language == 8) //kaonde
                        {
                            $message_string = "*What is your gender?* \n\n1. Male\n2. Female\n3. Other\n4. Prefer not to say";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 2,
                            "step_no" => 2
                        ]);

                        return $this->sendMessage($message_string, $phone_number, $from);

                    } elseif ($case_no == 2 && $step_no == 2 && !empty($user_message)) {
                        $gender = "Male";
                        if ($user_message == "1") {
                            $gender = "Male";
                        } elseif ($user_message == "2") {
                            $gender = "Female";
                        } elseif ($user_message == "3") {
                            $gender = "Other";
                        } elseif ($user_message == "2") {
                            $gender = "Prefer not to say";
                        } else {
                            $gender = "invalid input";
                        }

                        $save_data = DataSurvey::create([
                            "session_id" => $session_id,
                            "phone_number" => $from,
                            "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                            "channel" => "WhatsApp",
                            "question_number" => "3",
                            "question" => "What is your gender?",
                            "answer" => $user_message,
                            "answer_value" => $gender,
                            "telecom_operator" => $telecom_operator,
                            "data_category" => $data_category
                        ]);

                        $save_data->save();

                        if ($language == 1) //english
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 2) //nyanja
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 3) //bemba
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 4) //tonga
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 5) //lozi
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 6) //lunda
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 7) //luvale
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        } elseif ($language == 8) //kaonde
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 2,
                            "step_no" => 3
                        ]);

                        return $this->sendMessage($message_string, $phone_number, $from);

                    } elseif ($case_no == 2 && $step_no == 3 && !empty($user_message)) {
                        if ($user_message == "1" || $user_message == 1) {
                            //save the Lusaka district
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4",
                                "question" => "In which District do you live?",
                                "answer" => "1",
                                "answer_value" => "Lusaka",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 4
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        } elseif ($user_message == "2") {
                            //Kalomo District


                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
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

                            return $this->sendMessage($message_string, $phone_number, $from);

                        } elseif ($user_message == "3") {
                            //chavuma District

                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
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

                            return $this->sendMessage($message_string, $phone_number, $from);

                        } else {
                            //craft the error message and display the previous question
                            $error_message_string = "";

                            if ($language == 1) //english
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 3
                            ]);

                            $send_error_message = $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);
                        }


                    } elseif ($case_no == 2 && $step_no == 4 && !empty($user_message)) {
                        if ($user_message == "1" || $user_message == 1) {
                            //Chawama Constituency
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => "1",
                                "answer_value" => "Chawama",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi\n6. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 1
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }elseif($user_message == "2" || $user_message == 2) {
                            //Kabwata
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => "2",
                                "answer_value" => "Kabwata",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n5. Chilenje\n6. Kamulanga\n7. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 2
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }elseif ($user_message == "3" || $user_message == 3){
                            //Kanyama
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => "3",
                                "answer_value" => "Kanyama",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo\n5. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 3
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }elseif ($user_message == "4" || $user_message == 4){
                            //Lusaka Central
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "Which constituency do you live in?",
                                "answer" => "4",
                                "answer_value" => "Lusaka Central",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n5. Kabulonga \n6. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 4
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }elseif ($user_message == "5" || $user_message == 5){
                            //Mandevu
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => "5",
                                "answer_value" => "Mandevu",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 5
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }elseif ($user_message == "6" || $user_message == 6){
                            //Matero
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => "6",
                                "answer_value" => "Matero",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }elseif ($user_message == "7" || $user_message == 7){
                            //Munali
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "5",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => "7",
                                "answer_value" => "Munali",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu \n8. _Other(please specify)_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 7
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            $error_message_string = "";

                            if ($language == 1) //english
                            {
                                $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 5) //lozi
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "❌ You have selected an invalid option. \n";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 4
                            ]);

                            $send_error_message = $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);
                        }
                    }
                    break;
                case '3':
                    if($case_no == 3 && $step_no == 1 && !empty($user_message)){
                        //chosen Kabwata Ward
                        $chosen_ward = $user_message;

                        if($user_message == "1" || $user_message == 1){
                            $chosen_ward = "Kamwala";
                        }elseif ($user_message == "2" || $user_message == 2){
                            $chosen_ward = "Kabwata";
                        }elseif ($user_message == "3" || $user_message == 3){
                            $chosen_ward = "Libala";
                        }elseif ($user_message == "4" || $user_message == 4){
                            $chosen_ward = "Chilenje";
                        }elseif ($user_message == "5" || $user_message == 5){
                            $chosen_ward = "Kamulanga";
                        }

                        //save the selected ward
                        $save_data = DataSurvey::create([
                            "session_id" => $session_id,
                            "phone_number" => $from,
                            "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                            "channel" => "WhatsApp",
                            "question_number" => "6",
                            "question" => "In which Ward do you stay in?",
                            "answer" => "1",
                            "answer_value" => $chosen_ward,
                            "telecom_operator" => $telecom_operator,
                            "data_category" => $data_category
                        ]);

                        $save_data->save();

                        if ($language == 1) //english
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 2) //nyanja
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 3) //bemba
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 4) //tonga
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 5) //lozi
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 6) //lunda
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 7) //luvale
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 8) //kaonde
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 4,
                            "step_no" => 1
                        ]);

                        return $this->sendMessage($message_string, $phone_number, $from);


                    }elseif($case_no == 3 && $step_no == 2 && !empty($user_message)){
                        //
                        $chosen_ward = $user_message;

                        if($user_message == "1" || $user_message == 1){
                            $chosen_ward = "Nkoloma";
                        }elseif ($user_message == "2" || $user_message == 2){
                            $chosen_ward = "Chawama";
                        }elseif ($user_message == "3" || $user_message == 3){
                            $chosen_ward = "John Howard";
                        }elseif ($user_message == "4" || $user_message == 4){
                            $chosen_ward = "Lilayi";
                        }

                        //save the selected ward
                        $save_data = DataSurvey::create([
                            "session_id" => $session_id,
                            "phone_number" => $from,
                            "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                            "channel" => "WhatsApp",
                            "question_number" => "6",
                            "question" => "In which Ward do you stay in?",
                            "answer" => "1",
                            "answer_value" => $chosen_ward,
                            "telecom_operator" => $telecom_operator,
                            "data_category" => $data_category
                        ]);

                        $save_data->save();

                        if ($language == 1) //english
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 2) //nyanja
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 3) //bemba
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 4) //tonga
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 5) //lozi
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 6) //lunda
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 7) //luvale
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        } elseif ($language == 8) //kaonde
                        {
                            $message_string = "*Have you received a COVID 19 Vaccine?* \n1. Yes\n2. No";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 4,
                            "step_no" => 1
                        ]);

                        return $this->sendMessage($message_string, $phone_number, $from);
                    }else{
                        $error_message_string = "";

                        if ($language == 1) //english
                        {
                            $message_string = "*In which District do you live?* \n\n1. Lusaka \n2. Kalomo \n3. Chavuma";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 2) //nyanja
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 3) //bemba
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 4) //tonga
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 5) //lozi
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 6) //lunda
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 7) //luvale
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        } elseif ($language == 8) //kaonde
                        {
                            $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                            $error_message_string = "❌ You have selected an invalid option. \n";
                        }

                        $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                            "case_no" => 2,
                            "step_no" => 4
                        ]);

                        $send_error_message = $this->sendMessage($error_message_string, $phone_number, $from);
                        return $this->sendMessage($message_string, $phone_number, $from);

                    }
                    break;
                case '4': //
                    if($case_no == 4 && $step_no == 1 && !empty($user_message))
                    {

                    }
            }
        } else {
            Log::info('WhatsApp Error', ['no message' => json_encode($request)]);
        }

    }

    function sendMessage($message_string, $phone_number, $send_to)
    {
        $token = env('WHATSAPP_TOKEN');

        $send_message = Http::withHeaders([
            'headers' => ['Content-Type' => 'application/json']
        ])->post('https://graph.facebook.com/v12.0/' . $phone_number . '/messages?access_token=' . $token, [
            'messaging_product' => 'whatsapp',
            'to' => $send_to,
            'text' => ['body' => $message_string],
        ]);

        $responseBody = $send_message->body();
        Log::info('Send Message', ['response' => $responseBody]);

        return response('success', 200);
    }

    function sendImageMessage($phone_number, $send_to, $imageURL)
    {
        $token = env('WHATSAPP_TOKEN');

        $send_image_message = Http::withHeaders([
            'headers' => ['Content-Type' => 'application/json']
        ])->post('https://graph.facebook.com/v12.0/' . $phone_number . '/messages?access_token=' . $token, [
            'messaging_product' => 'whatsapp',
            'to' => $send_to,
            'type' => 'image',
            'image' => ['link' => $imageURL],
        ]);

        $responseBody = $send_image_message->body();
        Log::info('Send Message', ['response' => $responseBody]);

        return response('success', 200);
    }


}
