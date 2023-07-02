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
            $error_message_string = "";

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

                        $language_menu = "*MOH & Akros are conducting a survey. Choose language*\n\n";

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

                        if (is_numeric($user_message)) {
                            $chosen_language = "English";

                            if ($language == 1) //english
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                $chosen_language = "Nyanja";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                $chosen_language = "Bemba";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                $chosen_language = "Tonga";
                            } elseif ($language == 5) //kaonde
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                $chosen_language = "Kaonde";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                $chosen_language = "Lunda";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                $chosen_language = "Luvale";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 2
                            ]);

                            $ticked_language = "*LANGUAGE:* _" . $chosen_language . "_ ✅ ";
                            $selected_language = $this->sendMessage($ticked_language, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);
                        } else {
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

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 1,
                                "step_no" => 1
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);
                        }

                    } elseif ($case_no == 1 && $step_no == 2 && !empty($user_message)) {
                        if (is_numeric($user_message)) {
                            if ($user_message == "1" || $user_message == 1)// register account
                            {
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "1",
                                    "question" => "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?",
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
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Munani lilimo zekai (mun’ole lilimo) \n\n";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "What is your age? (Enter in years)";
                                } elseif ($language == 7) //luvale
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
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Thank you for your input, have a nice day";
                                }

                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "1",
                                    "question" => "This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?",
                                    "answer" => "2",
                                    "answer_value" => "No",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 1,
                                    "status" => 1 //terminate the session
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } else {
                                if ($language == 1) //english
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*This message is from researchers at MOH, ZNPHI, Akros, AFENET and the US CDC. Are you 18 or older and do we have your consent for this survey?.* \n\n1. Yes \n2. No";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 1,
                                    "step_no" => 1
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            }
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
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Akros niba liluko la makete (Ministry of Health) basweli kueza patisiso kuamana nibutata bobutisizwe ki butuku bwa Covid 19 kwa sicaba mwa naha Zambia. Haiba munani ni lilimo ze 18 kuya fahalimu mi mubata kuzwela pili,* \n\n1. Eni \n2. Batili";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*AKROS na Ministry ya Musokolwa ishasha utusokolwa. Nkashi lwandi watau masumu ya mundu kumweni kumukasanga?* \n\n1. Ehe \n2. Hae";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*AKROS na Mutundu wa Mbeu aveba shingwana shikongomelo. Uta landa vata ka mavilu kumabili na mwikaji, elacitandale?* \n\n1. Eyo \n2. Teya";
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
                        if (is_numeric($user_message)) {
                            //validate the age entered
                            if ($user_message >= 18) {
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
                                } elseif ($language == 5) //Kaonde
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
                            } else {
                                //if entered age is less than 18 years old
                                if ($language == 1) //english
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "Kindly note that this survey is only limited to individuals from the age of 18 years old and above";
                                }

                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "1",
                                    "question" => "Akros and Ministry of health are conducting a survey(if there’s need to specify the reason, it shall be done here). If you are 18 years or older and wish to proceed, press 1. if not press 2.",
                                    "answer" => "2",
                                    "answer_value" => $user_message,
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 2,
                                    "step_no" => 1,
                                    "status" => 1 //terminate session
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            }
                        } else {
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
                            } elseif ($language == 5) //Kaonde
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

                        }

                    } elseif ($case_no == 2 && $step_no == 2 && !empty($user_message)) {
                        if (is_numeric($user_message)) {

                            $gender = "invalid input";

                            if ($user_message == 1){
                                $gender = "Male";
                            }else if ($user_message == 2) {
                                $gender = "Female";
                            } elseif ($user_message == 3) {
                                $gender = "Other";
                            } elseif ($user_message == 4) {
                                $gender = "Prefer not to say";
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
                            } elseif ($language == 5) //Kaonde
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
                        } else {
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
                            } elseif ($language == 5) //Kaonde
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
                        }

                    } elseif ($case_no == 2 && $step_no == 3 && !empty($user_message)) {
                        //Save selected District and Ask for respective constituency
                        if (is_numeric($user_message)) {
                            if ($user_message == 1) {
                                //save the Lusaka district
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4a",
                                    "question" => "In which District do you live?",
                                    "answer" => "1",
                                    "answer_value" => "Lusaka District",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 1 //save Constituency in Lusaka
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 2) {
                                //Kalomo District
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4a",
                                    "question" => "In which District do you live?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kalomo District",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 4,
                                    "step_no" => 1 //save Kalomo District Constituency and go ask about wards
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 3) {
                                //chavuma District
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4a",
                                    "question" => "In which District do you live?",
                                    "answer" => $user_message,
                                    "answer_value" => "Chavuma District",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 5,
                                    "step_no" => 1
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } else {
                                //craft the error message and display the previous question

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

                                } elseif ($language == 5) //Kaonde
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
                            }
                        } else {
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
                            } elseif ($language == 5) //Kaonde
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
                        }
                    }
                    break;
                case '3':
                    if($case_no == 3 && $step_no == 1 && !empty($user_message)){
                        //Lusaka District
                        if (is_numeric($user_message) && $user_message >= 1 && $user_message <= 7) {
                            if ($user_message == 1) {
                                //Chawama Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Chawama Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 2 // save Chawama Constituency ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 2) {
                                //Kabwata Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kabwata Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 3 //save Kabwata ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 3) {
                                //Kanyama Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kanyama Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 4 //save Kanyama ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 4) {
                                //Lusaka Central Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "Which Constituency do you live in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Lusaka Central Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 5 //save Lusaka Central ward, ask about Covid and go to Case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 5) {
                                //Mandevu Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Mandevu Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 6 //save Mandevu ward, ask about Covid and go to Case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 6) {
                                //Matero Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Matero Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 7 //save Matero Ward, ask about Covid and go to case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            } elseif ($user_message == 7) {
                                //Munali Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Munali Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 3,
                                    "step_no" => 8 //save Munali Ward, ask about Covid and go to Case 6
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);

                            }
                        } else {
                            $error_message_string = "⚠️ _You have entered an invalid input!_";

                            if ($language == 1) //english
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n1. Chawama \n2. Kabwata \n3. Kanyama \n4. Lusaka Central \n5. Mandevu \n6. Matero \n7. Munali";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 2,
                                "step_no" => 3
                            ]);

                            $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);
                        }
                    }elseif ($case_no == 3 && $step_no == 2 && !empty($user_message)){
                        //save Chawama Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 4){
                            $ward = "Nkoloma";

                            if ($user_message == 2){
                                $ward = "Chawama";
                            }elseif ($user_message == 3)
                            {
                                $ward = "John Howard";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Lilayi";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3. John Howard\n5. Lilayi";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Nkoloma\n2. Chawama\n3.John Howard\n5.Lilayi";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 2 //Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);
                        }
                    }elseif ($case_no == 3 && $step_no == 3 && !empty($user_message)){
                        //save Kanyama Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 6){
                            $ward = "Kamwala";

                            if($user_message == 2)
                            {
                                $ward = "Kabwata";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Libala";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chilenje";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Kamulanga";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kamwala\n2. Kabwata\n3. Libala\n4. Chilenje\n5. Kamulanga";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 3 //save Kabwata ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 3 && $step_no == 4 && !empty($user_message)){
                        //save Lusaka Central Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 3){

                            $ward = "Kanyama";
                            if($user_message == 2)
                            {
                                $ward = "Harry Mwanga Nkumbula";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Munkolo";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);


                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Kanyama\n2. Harry Mwanga Nkumbula\n3. Munkolo";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 4 //save Kanyama ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 3 && $step_no == 5 && !empty($user_message)){
                        //save Lusaka Central Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <=4){
                            $ward = "Silwizya";

                            if($user_message == 2)
                            {
                                $ward = "Independence";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Lubwa";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Kabulonga";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Silwizya\n2. Independence\n3. Lubwa\n4. Kabulonga ";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 5 //save Lusaka Central ward, ask about Covid and go to Case 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 3 && $step_no == 6 && !empty($user_message)){
                        //save Mandevu Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 7){
                            $ward = "Roma";

                            if($user_message == 2)
                            {
                                $ward = "Mulungushi";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Ngwerere";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chaisa";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Justine Kabwe";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Raphael Chota";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Mpulungu";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Roma\n2. Mulungushi\n3. Ngwerere\n4. Chaisa\n5. Justine Kabwe\n6. Raphael Chota\n7. Mpulungu ";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 6 //save Mandevu ward, ask about Covid and go to Case 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 3 && $step_no == 7 && !empty($user_message)){
                        //save Matero Ward
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 5){
                            $ward = "Muchinga";

                            if($user_message == 2)
                            {
                                $ward = "Kapwepwe";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Lima";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Mwembeshi";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Matero";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Muchinga\n2. Kapwepwe\n3. Lima\n4. Mwembeshi\n5. Matero";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 7 //save Matero Ward, ask about Covid and go to case 6
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 3 && $step_no == 8 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 7)
                        {
                            $ward = "Chainda";

                            if($user_message == 2)
                            {
                                $ward = "Mtendere";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Kalingalinga";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chakunkula";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Munali";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Chelstone";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Avondale";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{

                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chainda\n2. Mtendere\n3. Kalingalinga\n4. Chakunkula\n5. Munali\n6. Chelstone\n7. Avondale ";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 3,
                                "step_no" => 8 //save Munali Ward, ask about Covid and go to Case 6
                            ]);

                            $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }
                    break;
                case '4': //show Kalomo District Wards
                    if ($case_no == 4 && $step_no == 1 && !empty($user_message)) {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 2)
                        {
                            if($user_message == 1)
                            {
                                //Dundumwezi Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Dundumwezi Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                //ask about Dundumwezi wards
                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 4,
                                    "step_no" => 2 // save Dundumwezi Constituency ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);


                            }elseif ($user_message == 2)
                            {
                                //Kalomo Central Constituency
                                $save_data = DataSurvey::create([
                                    "session_id" => $session_id,
                                    "phone_number" => $from,
                                    "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                    "channel" => "WhatsApp",
                                    "question_number" => "4b",
                                    "question" => "In which Constituency do you stay in?",
                                    "answer" => $user_message,
                                    "answer_value" => "Kalomo Central Constituency",
                                    "telecom_operator" => $telecom_operator,
                                    "data_category" => $data_category
                                ]);

                                $save_data->save();

                                //ask about Kalomo Central Wards
                                if ($language == 1) //english
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 2) //nyanja
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 3) //bemba
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 4) //tonga
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 5) //Kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 6) //lunda
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 7) //luvale
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                } elseif ($language == 8) //kaonde
                                {
                                    $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                }

                                $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                    "case_no" => 4,
                                    "step_no" => 3 // save Kalomo Central Constituency ward, Ask about Covid then go to Case 6 to proceed
                                ]);

                                return $this->sendMessage($message_string, $phone_number, $from);


                            }

                        }else{
                            //repeat constituency question
                            if ($language == 1) //english
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Dundumwezi \n2. Kalomo Central";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 4,
                                "step_no" => 1 //save Kalomo District Constituency and go ask about wards
                            ]);

                            $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 4 && $step_no == 2 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 6)
                        {
                            $ward = "Chikanta";

                            if($user_message == 2)
                            {
                                $ward = "Chamuka";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Kasukwe";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Omba";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Bbili";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Naluja";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chikanta\n2. Chamuka\n3. Kasukwe\n4. Omba\n5. Bbili\n6. Naluja";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 4,
                                "step_no" => 2 // save Dundumwezi Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 4 && $step_no == 3 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 9)
                        {
                            $ward = "Siachitema";

                            if($user_message == 2)
                            {
                                $ward = "Kalonda";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Choonga";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Mayoba";
                            }elseif ($user_message == 5)
                            {
                                $ward = "Namwianga";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Simayakwe";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Chawila";
                            }elseif ($user_message == 8)
                            {
                                $ward = "Sipatunyana";
                            }elseif ($user_message == 9)
                            {
                                $ward = "Nachikungu";
                            }

                            //save ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Siachitema\n2. Kalonda\n3. Choonga\n4. Mayoba\n5. Namwianga\n6. Simayakwe\n7. Chawila\n8. Sipatunyana\n9. Nachikungu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 4,
                                "step_no" => 3 // save Kalomo Central Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            $this->sendMessage($error_message_string,$phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }
                case '5'://Chavuma District Wards
                    if ($case_no == 5 && $step_no == 1 && !empty($user_message)) {
                        if(is_numeric($user_message) && $user_message == 1)
                        {
                            //save data
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4b",
                                "question" => "In which Constituency do you stay in?",
                                "answer" => $user_message,
                                "answer_value" => "Chavuma Constituency",
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            //ask which ward do they live in
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 5,
                                "step_no" => 2 // save Chavuma Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            //repeat constituency question
                            if ($language == 1) //english
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Which constituency do you live in?* \n\n1. Chavuma";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 5,
                                "step_no" => 1 //save Kalomo District Constituency and go ask about wards
                            ]);

                            $this->sendMessage($error_message_string, $phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }elseif ($case_no == 5 && $step_no == 2 && !empty($user_message))
                    {
                        if(is_numeric($user_message) && $user_message >= 1 && $user_message <= 13)
                        {
                            $ward = "Chambi Mandalo";

                            if($user_message == 2)
                            {
                                $ward = "Sewe";
                            }elseif ($user_message == 3)
                            {
                                $ward = "Lingelengenda";
                            }elseif ($user_message == 4)
                            {
                                $ward = "Chiyeke";
                            }elseif ($user_message == 5)
                            {
                                $ward = "KalomboKamisamba";
                            }elseif ($user_message == 6)
                            {
                                $ward = "Chivombo Mbelango";
                            }elseif ($user_message == 7)
                            {
                                $ward = "Chavuma central";
                            }elseif ($user_message == 8)
                            {
                                $ward = "Sanjongo";
                            }elseif ($user_message == 9)
                            {
                                $ward = "Lingundu";
                            }elseif ($user_message == 10)
                            {
                                $ward = "Lukolwe Musumba";
                            }elseif ($user_message == 11)
                            {
                                $ward = "Kambuya Mukelengombe";
                            }elseif ($user_message == 12)
                            {
                                $ward = "Nyalanda Nyambingala";
                            }elseif ($user_message == 13)
                            {
                                $ward = "Nguvu";
                            }

                            //save the ward
                            $save_data = DataSurvey::create([
                                "session_id" => $session_id,
                                "phone_number" => $from,
                                "language_id" => WhatsAppSession::where('session_id', $session_id)->first()->language_id,
                                "channel" => "WhatsApp",
                                "question_number" => "4c",
                                "question" => "In which Ward do you live?",
                                "answer" => $user_message,
                                "answer_value" => $ward,
                                "telecom_operator" => $telecom_operator,
                                "data_category" => $data_category
                            ]);

                            $save_data->save();

                            if ($language == 1) //english
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*Have you received a COVID-19 vaccine?*\n\n1. Yes\n2. No";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 6,
                                "step_no" => 1 //save Covid vaccine
                            ]);

                            return $this->sendMessage($message_string, $phone_number, $from);

                        }else{
                            if ($language == 1) //english
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 2) //nyanja
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 3) //bemba
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 4) //tonga
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 5) //Kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 6) //lunda
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 7) //luvale
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            } elseif ($language == 8) //kaonde
                            {
                                $message_string = "*In which Ward do you stay in?* \n1. Chambi Mandalo\n2. Sewe\n3. Lingelengenda\n4. Chiyeke\n5. KalomboKamisamba\n6. Chivombo Mbelango\n7. Chavuma central\n8. Sanjongo\n9. Lingundu\n10. Lukolwe Musumba\n11. Kambuya Mukelengombe\n12. Nyalanda Nyambingala\n13. Nguvu";
                                $error_message_string = "⚠️ _You have entered an invalid input!_";
                            }

                            $update_session = WhatsAppSession::where('session_id', $session_id)->update([
                                "case_no" => 5,
                                "step_no" => 2 // save Chavuma Constituency ward, Ask about Covid then go to Case 6 to proceed
                            ]);

                            $this->sendMessage($error_message_string,$phone_number, $from);
                            return $this->sendMessage($message_string, $phone_number, $from);

                        }
                    }
                case '6'://Next Questions
                    if ($case_no == 6 && $step_no == 1 && !empty($user_message)) {
                        if(is_numeric($user_message))
                        {
                            //save data
                        }else{
                            //repeat wards question
                        }
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
