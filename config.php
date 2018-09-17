
<?php 
$config = array(
    "SIP_CONFIG_PATH"=>"/etc/testconfig/",
    "SIP_CONFIG_FILE"=>"sip-d.json",
    "SIP_FILES"=>[
        1=>"rapidvox.json",
        2=>"nexmo.json",
        3=>"twilio.json"
    ],
    "THRESHOLD"=>5, //call fail limit after which sip will switch
    "LOG_FILE"=>"/tmp/api.log"
);

