<?php
/**
 * Plugin Name: شبیه ساز امیر بهرامی
 * Description: یه پلاگین واسه شبیه سازی امیر بهرامی [ai_chatbox].
 * Version: 1.8
 * Author: Amir Bahrami
 */

// please put your OpenAI API key at line 187
// you can also change the plugin info in the begining of this file
// you can change the personality and info of the AI Character by changin the string at line 207
// after personalizing and activing the plugin you can use the chat box everywhere in your website using [ai_chatbox] short code !
// You can contact me at https://amirbhr.ir if you needed help with this



// Add shortcode [ai_chatbox]
function ai_chatbox_shortcode() {
    ob_start();
    ?>
    <div id="chatbox" dir="rtl">
        <div id="chatlog">
            <div class='botMessage'><strong>امیرمحمد بهرامی:</strong> سلام ، چطور میتونم کمکتون کنم ؟</div>
        </div>
        <input type="text" id="userInput" placeholder="پیام خود را بنویسید..." />
        <button id="sendBtn">ارسال</button>
    </div>
    <style>
        #chatbox { 
            border: 1px solid #ccc; 
            padding: 10px; 
            width: 100%; /* ریسپانسیو شدن عرض باکس */
            box-sizing: border-box;
        }
        #chatlog { 
            height: 300px; 
            overflow-y: scroll; 
            margin-bottom: 10px; 
            text-align: right;
        }
        #userInput { 
            width: 70%; 
            text-align: right; 
            padding: 8px;
        }
        #sendBtn { 
            width: 25%; 
            background-color: #0B77B9; 
            color: white; 
            border: none; 
            padding: 8px; 
            cursor: pointer; 
        }
        #sendBtn:hover {
            background-color: #065a91;
        }
        /* تنظیم پیام‌ها */
        .userMessage {
            background-color: #0B77B9;
            color: white;
            padding: 10px;
            border-radius: 15px;
            margin: 5px;
            text-align: right;
            max-width: 80%;
            float: right;
            clear: both;
        }
        .botMessage {
            background-color: white;
            color: black;
            padding: 10px;
            border-radius: 15px;
            margin: 5px;
            text-align: left;
            max-width: 80%;
            float: left;
            clear: both;
            border: 1px solid #ccc;
        }
        .typingIndicator {
            background-color: white;
            color: grey;
            font-style: italic;
            padding: 10px;
            border-radius: 15px;
            margin: 5px;
            text-align: left;
            max-width: 80%;
            float: left;
            clear: both;
        }
    </style>
    <script>
        let initialHeight = window.innerHeight; // ارتفاع اولیه صفحه
        let isKeyboardOpen = false;

        // Function to send a message
        function sendMessage() {
            var userInput = document.getElementById("userInput").value;
            if (userInput === "") return;
            
            var chatLog = document.getElementById("chatlog");
            chatLog.innerHTML += "<div class='userMessage'><strong>شما:</strong> " + userInput + "</div>";
            document.getElementById("userInput").value = "";
            chatLog.scrollTop = chatLog.scrollHeight;

            // نمایش پیام "در حال تایپ کردن..."
            var typingMessage = document.createElement("div");
            typingMessage.className = 'typingIndicator';
            typingMessage.innerHTML = "<strong>امیرمحمد بهرامی:</strong> در حال تایپ کردن...";
            chatLog.appendChild(typingMessage);
            chatLog.scrollTop = chatLog.scrollHeight;

            // ارسال پیام به سرور
            fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=ai_chatbox_ask", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ message: userInput })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);  // نمایش پاسخ در کنسول برای بررسی
                if (data.success && data.data.response) {
                    typingMessage.className = 'botMessage'; // تغییر کلاس از typing به botMessage
                    typingMessage.innerHTML = "<strong>امیرمحمد بهرامی:</strong> " + data.data.response;
                } else {
                    typingMessage.className = 'botMessage'; // نمایش پیام خطا
                    typingMessage.innerHTML = "<strong>امیرمحمد بهرامی:</strong> Error: " + data.data;
                }
                chatLog.scrollTop = chatLog.scrollHeight;
            })
            .catch(error => {
                console.error("Error:", error);
                typingMessage.className = 'botMessage';
                typingMessage.innerHTML = "<strong>امیرمحمد بهرامی:</strong> Error: Failed to send request.";
                chatLog.scrollTop = chatLog.scrollHeight;
            });

            // بستن کیبورد در دستگاه‌های موبایل و تبلت
            document.getElementById("userInput").blur();
        }

        // Event listener for the "Send" button
        document.getElementById("sendBtn").addEventListener("click", function() {
            sendMessage();
        });

        // Event listener for pressing "Enter"
        document.getElementById("userInput").addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault(); // جلوگیری از ایجاد خط جدید در ورودی متن
                sendMessage();
            }
        });

        // Check if keyboard is opening on mobile devices
        window.addEventListener("resize", function() {
            const currentHeight = window.innerHeight;

            if (window.innerWidth <= 768) { // موبایل و تبلت
                if (currentHeight < initialHeight) {
                    isKeyboardOpen = true; // کیبورد باز شده است
                    // اسکرول کردن صفحه به باکس چت
                    var chatBox = document.getElementById("chatbox");
                    chatBox.scrollIntoView({ behavior: "smooth" });
                } else {
                    isKeyboardOpen = false; // کیبورد بسته است
                }
            }
        });

        // Update initialHeight on page load or orientation change
        window.addEventListener("orientationchange", function() {
            initialHeight = window.innerHeight;
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('ai_chatbox', 'ai_chatbox_shortcode');

// Handle AJAX request
function ai_chatbox_handle_ajax() {
    $api_key = 'YOUR_OPENAI_API_KEY'; // حتماً کلید API خود را وارد کنید
    $message = json_decode(file_get_contents('php://input'), true)['message'];

    if (empty($api_key)) {
        wp_send_json_error('API key is missing.');
    }

    if (empty($message)) {
        wp_send_json_error('User message is empty.');
    }

    // Call OpenAI API with increased timeout
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body' => json_encode(array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array('role' => 'system', 'content' => 'if u wanted to send any links or urls to user make sure that u send it in a <a href="URL"> URL </a> format that it be click able , Your name is Amirmohammad (امیرمحمد) and your lastname is Bahrami (بهرامی) . you are a fullstack developer. You are the owner and CEO of Bahrami Digital Marketing Agency , Melkshot group , Etemad AI platform and Bahrami AI Academy. some info that you may need : (your phone number : 09025475393 ; your instagram id : @dr_amirbahrami ; your telegram id : @dr_amirbhr ; your linked in link : https://www.linkedin.com/in/dr-amirmohammad-bahrami/ ; your Adress : کرج ، مهرشهر ، بلوار ارم . your services : Web design , Application programming , IT training courses , Marketing and ADS , Branding , Automation , CRM and ERP , Network ; your birthdate : 12 ام خرداد سال 1384 ; your fathers name : داود ; your mothers name : فاطمه ; you have 2 little sistes that they are twins (Yekta and Bita)) . if user asked for services or price of services send him your contact info and ask him to call ; if user needed Expert advice send him contact info to call . '),

                array('role' => 'user', 'content' => $message),
            ),
            'temperature' => 0.7,
            'max_tokens' => 2048, // افزایش تعداد توکن‌ها برای دریافت پاسخ‌های طولانی‌تر
        )),
        'timeout' => 30, // افزایش محدودیت تایم‌اوت به 30 ثانیه
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed: ' . $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // بررسی ساختار JSON دریافت شده
    if (isset($body['choices'][0]['message']['content'])) {
        $ai_response = $body['choices'][0]['message']['content'];
        wp_send_json_success(array('response' => $ai_response));
    } else {
        wp_send_json_error('Invalid API response: ' . wp_remote_retrieve_body($response));
    }
}
add_action('wp_ajax_ai_chatbox_ask', 'ai_chatbox_handle_ajax');
add_action('wp_ajax_nopriv_ai_chatbox_ask', 'ai_chatbox_handle_ajax');
