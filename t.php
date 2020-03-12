<?php

function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if(!$from){
		$from = 'Automated Action <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
 	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
	if($bcc){
		$headers .= "BCC: ".$bcc." \r\n";
	}
	
	//echo "Sending mail subject [".$subject."] to [".$to."] bcc [".$bcc."] from [".$from."] headers [<br>".nl2br($headers)."<br>]with the following message <hr>".$message."<hr>";	
    return mail($to,$subject,$message,$headers);
}

$message = 'Date is -> ['.date('Y-m-d H:i:s').']

About Young Workers

The Young Workers. Ministry was established in 1998 to address needs peculiar to young working people. Since its inception, the ministry has organized several events and activities in all districts of Watoto Church.

We are of a community of over 800 people and are one of the core ministries in Watoto Church. The ministry is based in all districts of Watoto Church.

The Objectives of the ministry are to; Equip young workers with skills to promote Christian values at the work place; Provide forums that address the spiritual, social-economic, emotional

and physical needs of Young Workers; Encourage young workers to identify and use their gifts to serve in the body of Christ; Reach out to the community by sharing the love of Jesus Christ;

Partner with other ministries within and outside Watoto Church to further the Kingdom of God.

 

Vision

To be a ministry that reaches out and mentors young working people in Uganda for Christ.

 

Mission

To build fulfilled and effective Christian Young workers to impact the community for the Glory of God.

 

Young Workers Ministry Values

Responsibility | Integrity | Humility | Purity
<pre>'.shell_exec('/sbin/ifconfig').'</pre>';

$result = sendHTMLemail($to='sntaven@gmail.com,ccbusinessanalysis@waridtel.co.ug',$bcc,$message=nl2br($message),$subject='Test from WIMAXCRM WEB',$from);

echo date('Y-m-d H:i:s')." Mail result -> [".$result."]<hr>\n<pre>".shell_exec('/sbin/ifconfig')."</pre>";

?>
