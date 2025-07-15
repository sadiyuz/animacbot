<?php
ob_start();
error_reporting(0);
date_Default_timezone_set('Asia/Tashkent');

/**
* Ushbu kodni www.saidabror.uz ( Tokhtasinov Saidabror ) tuzib chiqgan.
* Mehnatimni qadrlaysz degan umiddaman. Hammaga raxmat !!!
* @author Tokhtasinov Saidabror
* @link https://www.saidabror.uz
* @link https://t.me/sadiyuz
*/

define('BOT_TOKEN',"7590608154:AAF54GauYbQ832v7bTu3FQbjpws5rjHfYmk");
$admin = "6914992231";
$owners = array($admin);
$bot = bot('getme',['bot'])->result->username;
$soat = date('H:i');
$sana = date("d.m.Y");

require "sql.php";

function getAdmin($chat){
$url = "https://api.telegram.org/bot".BOT_TOKEN."/getChatAdministrators?chat_id=@".$chat;
$result = file_get_contents($url);
$result = json_decode ($result);
return $result->ok;
}

function deleteFolder($path){
if(is_dir($path) === true){
$files = array_diff(scandir($path), array('.', '..'));
foreach ($files as $file)
deleteFolder(realpath($path) . '/' . $file);
return rmdir($path);
}else if (is_file($path) === true)
return unlink($path);
return false;
}

function viewAnime($chat_id,$anime_id){
global $connect;
$rew=mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM anime_views WHERE user_id = $chat_id AND anime_id = $anime_id"));
if(!$rew){
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
$views=$row['views']+1;
mysqli_query($connect,"UPDATE animes SET views=$views WHERE id = $anime_id");
mysqli_query($connect,"INSERT INTO anime_views(user_id,anime_id) VALUES ('$chat_id','$anime_id')");
}
}

function accl($d,$s,$j=false){
return bot('answerCallbackQuery',[
'callback_query_id'=>$d,
'text'=>$s,
'show_alert'=>$j
]);
}

function bot($method,$datas=[]){
	$url = "https://api.telegram.org/bot".BOT_TOKEN."/".$method;
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
	$res = curl_exec($ch);
	if(curl_error($ch)){
		var_dump(curl_error($ch));
	}else{
		return json_decode($res);
	}
}

function delkey(){
global $cid,$cid2;
$emid=sms($cid.$cid2,"⏳",json_encode(['remove_keyboard'=>true]))->result->message_id;
bot('deleteMessage',[
'chat_id'=>$cid2.$cid,
'message_id'=>$emid,
]);
}

function del(){
global $cid,$mid,$chat_id,$message_id;
return bot('deleteMessage',[
'chat_id'=>$chat_id.$cid,
'message_id'=>$message_id.$mid,
]);
}


function edit($id,$mid,$tx,$m){
return bot('editMessageText',[
'chat_id'=>$id,
'message_id'=>$mid,
'text'=>$tx,
'parse_mode'=>"HTML",
'disable_web_page_preview'=>true,
'reply_markup'=>$m,
]);
}



function sms($id,$tx,$m){
return bot('sendMessage',[
'chat_id'=>$id,
'text'=>$tx,
'parse_mode'=>"HTML",
'disable_web_page_preview'=>true,
'reply_markup'=>$m,
]);
}

function get($h){
return file_get_contents($h);
}

function put($h,$r){
file_put_contents($h,$r);
}

function joinchat($id,$start=null){
global $connect;
$res=mysqli_query($connect,"SELECT * FROM channels");
$c=mysqli_num_rows($res);
if($c > 0){
$no_subs=0;
$request = json_decode(get('requests.json'),1);
while($a=mysqli_fetch_assoc($res)){
$ty = $a['type'];
$channelID = $a['channelID'];
if($ty == "public"){
$stat=bot('getChatMember',['chat_id'=>$channelID,'user_id'=>$id])->result->status;
$getchat=bot('getchat',['chat_id'=>$channelID]);
if($stat != "administrator" and $stat != "member" and $stat != "creator"){
$uz[]=['text'=>"❌ ".($getchat->result->title)."",'url'=>$a['link']];
$no_subs++;
}else{
$uz[]=['text'=>"✅ ".($getchat->result->title)."",'url'=>$a['link']];
}
}elseif($ty == "request"){
$getchat=bot('getchat',['chat_id'=>$channelID]);
if(in_array($id,$request[$channelID])){
$uz[]=['text'=>"✅ ".($getchat->result->title)."",'url'=>$a['link']];
}else{
$uz[]=['text'=>"❌ ".($getchat->result->title)."",'url'=>$a['link']];
$no_subs++;
}
}elseif($ty == "optional"){
$uz[]=['text'=>"🔥 ".$a['name']."",'url'=>$a['link']];
}
}
$keyboard2=array_chunk($uz,1);
if($start !== null){
$keyboard2[]=[['text'=>"Tekshirish ♻️",'callback_data'=>"checkSub=$start"]];
}else{
$keyboard2[]=[['text'=>"Tekshirish ♻️",'callback_data'=>"checkSub=none"]];
}
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
if($no_subs>0){
sms($id,"<b>💭Avval homiy</b> kanallarimizga to'liq <b>obuna bo'ling 👇</b>",$kb);
exit;
}else return true;
}else return true;
}

function staff($cid){
global $connect,$owners;
$result=mysqli_query($connect,"SELECT * FROM admins WHERE user_id = $cid");
$row=mysqli_fetch_assoc($result);
if($row or in_array($cid,$owners)){
return true;
}
return false;
}

$update = json_decode(file_get_contents('php://input'));
$request = json_decode(get('requests.json'),1);
$message = $update->message;
$cid = $message->chat->id;
$mid = $message->message_id;
$type = $message->chat->type;
$text = $message->text;
$uid = $message->from->id;
$name = $message->from->first_name;

$chat_join_request = $update->chat_join_request;
if(isset($chat_join_request)){
$chat = $chat_join_request->chat->id;
$user = $chat_join_request->from->id;
if(!isset($request[$chat])){
$request[$chat] = [];
}
if(!in_array($user, $request[$chat])){
$request[$chat][] = $user;
file_put_contents('requests.json', json_encode($request, JSON_PRETTY_PRINT));
}
}

//inline uchun metodlar
$data = $update->callback_query->data;
$qid = $update->callback_query->id;
$cid2 = $update->callback_query->message->chat->id;
$mid2 = $update->callback_query->message->message_id;
$uid2 = $update->callback_query->from->id;
$name2 = $update->callback_query->from->first_name;
$chat_id=$cid2;
$message_id=$mid2;

$photo = $message->photo;
$file = $photo[count($photo)-1]->file_id;
$anime_kanal = file_get_contents("admin/anime_kanal.txt");
$kanal = file_get_contents("admin/kanal.txt");

$user=mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM users WHERE id=$cid$chat_id"));
$step=$user['step'];

// By: t.me/sadiyuz

mkdir("tizim");
mkdir("step");
mkdir("admin");

$panel = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🆕Anime qo'shish"]],
[['text'=>"➕Seriya qo'shish"]],
[['text'=>"✏️Anime tahrirlash"],['text'=>"✏️Seriya tahrirlash"]],
[['text'=>"📬 Post tayyorlash"]],
[['text'=>"📊Statik ma'lumotlar"]],
[['text'=>"💬Xabar yuborish"],['text'=>"👤Alohida xabar"]],
[['text'=>"🔐Majburiy a'zo"],['text'=>"👔Staff qo'shish"]],
[['text'=>"🔙Chiqish"]]
]
]);

$menu = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🔎 Anime izlash"]],
[['text'=>"🔴 Shorts bo'lim"],['text'=>"📚 Qo'llanma"]],
[['text'=>"💵 Reklama va Homiylik"]]
]
]);

$back = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"◀️ Orqaga"]],
]
]);

$aort=json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🔙Ortga"]],
]
]);

//<---- @obito_is ---->//

if($ban == "ban") exit();

if(isset($message)){
$result = mysqli_query($connect,"SELECT * FROM users WHERE id = $cid");
$row = mysqli_fetch_assoc($result);
if(!$row){
mysqli_query($connect,"INSERT INTO users(`id`,`ban`,`step`) VALUES ('$cid','0','none')");
}
}

if(strtolower($text)=="/start" or $text=="◀️ Orqaga"){	
sms($cid,"<b>Assalomu alaykum!
Botimizga xush kelibsiz ✌️</b>",$menu);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid");
exit();
}

if($text=="🔙Chiqish"){
delkey();
sms($cid,"/start ni ezing✅",null);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid");
}

if($data=="result" or $data=="ortga"){
del();
if(joinchat($cid2)==true){
sms($cid2,"<b>Assalomu alaykum!
Botimizga xush kelibsiz ✌️</b>",$menu);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid2");
exit();
}
}

if(stripos($data,"checkSub=")!==false){
$id = explode("=",$data)[1];
del();
if(joinchat($cid2)==true){
if($id == "none"){
sms($cid2,"<b>Assalomu alaykum!
Botimizga xush kelibsiz ✌️</b>",$menu);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid2");
exit();
}
}
}

// By: t.me/sadiyuz

if($data=="close")del();

if($text=="🔎 Anime izlash" and joinchat($cid)==1){
sms($cid,"Quidagilardan birini tanlang:",json_encode([
'inline_keyboard'=>[
[['text'=>"🔎 Nom bo‘yicha izlash",'callback_data'=>"searchByName"]],
[['text'=>"🔎 Kod bo‘yicha izlash",'callback_data'=>"searchById"]],
[['text'=>"🔎 Barcha animelar",'callback_data'=>"allanimes"]],
[['text'=>"🔎 Eng ko‘p ko‘rilgan animelar",'callback_data'=>"topViewers"]],
]]));
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}

if($data=="searchById"){
del();
sms($chat_id,"🔍Qidirish uchun anime kodini yuboring !",$back);
mysqli_query($connect,"UPDATE users SET step = '$data' WHERE id = $chat_id");
}

if($step=="searchById" and is_numeric($text)){
$rew = mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $text");
$c = mysqli_num_rows($rew);
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>$a['title'],'callback_data'=>"loadAnime=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$keyboard2[]=[['text'=>"❌ Yopish",'callback_data'=>"close"]];
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
if(!$c){
sms($cid,"🙁 Natija mavjud emas!",null);
exit;
}else{
bot('sendMessage',[
'chat_id'=>$cid,
'reply_to_message_id'=>$mid,
'text'=>"<b>Qidiruvi boʻyicha natijalar: $c ta</b>",
'parse_mode'=>"html",
'reply_markup'=>$kb
]);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid");
exit;
}
}

if($data=="searchByName"){
del();
sms($chat_id,"🔍Qidirish uchun anime nomini yuboring !",$back);
mysqli_query($connect,"UPDATE users SET step = '$data' WHERE id = $chat_id");
}

if($step=="searchByName" and isset($text)){
$text = mysqli_real_escape_string($connect,$text);
$rew = mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%'");
$c = mysqli_num_rows($rew);
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>$a['title'],'callback_data'=>"loadAnime=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$keyboard2[]=[['text'=>"❌ Yopish",'callback_data'=>"close"]];
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
if(!$c){
sms($cid,"🙁 Natija mavjud emas!",null);
exit;
}else{
bot('sendMessage',[
'chat_id'=>$cid,
'reply_to_message_id'=>$mid,
'text'=>"<i>$text</i> <b>qidiruvi boʻyicha natijalar: $c ta</b>",
'parse_mode'=>"html",
'reply_markup'=>$kb
]);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid");
exit;
}
}

// By: t.me/sadiyuz


if($data=="allanimes"){
del();
$allanime=mysqli_query($connect,"SELECT*FROM animes");
while($a=mysqli_fetch_assoc($allanime)){
$uz[]=['text'=>$a['title']];
}
$keyboard2=array_chunk($uz,2);
$keyboard2[]=[['text'=>"◀️ Orqaga"]];
$allanimes=json_encode([
'resize_keyboard'=>true,
'keyboard'=>$keyboard2
]);
sms($chat_id,"Ushbu bo‘limda siz barcha Animelarni topishingiz mumkin ✨️",$allanimes);
mysqli_query($connect,"UPDATE users SET step = 'searchByName' WHERE id = $chat_id");
}

if($data=="topViewers"){
$res=mysqli_query($connect,"SELECT * FROM `animes` ORDER BY `views` DESC LIMIT 10");
$i=1;
$text="";
while($row=mysqli_fetch_assoc($res)){
$text.="<b>$i)</b> ".$row['title']." - ".$row['views']."\n";
$i++;
}
sms($chat_id,"Botdagi eng ko'p ko'rilgan Anime lar ro'yhati: 👇\n\n$text",$menu);
}

if(mb_stripos($data,"loadAnime=")!==false){
$n=explode("=",$data)[1];

$uzpage = 1;
$ufset = ($uzpage-1) * 50; 

del();
viewAnime($chat_id,$n);
$rew=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $n"));
if($rew){
$a=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `anime_datas` WHERE `id` = $n ORDER BY `qism` ASC LIMIT 1"));
sms($chat_id,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
if($rew['lang']=="uz"){
$til="o‘zbek";
}elseif($rew['lang']=="ru"){
$til="rus";
}elseif($rew['lang']=="en"){
$til="ingliz";
}elseif($rew['lang']=="jp"){
$til="yapon";
}
$res=mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $n LIMIT $ufset,50");
while($row=mysqli_fetch_assoc($res)){
$keys[]=['text'=>"".$row['episode']."",'callback_data'=>"watchAnime=$n=".$row['episode'].""];
}

$keyboard2=array_chunk($keys,5);

$totalrows = mysqli_num_rows(mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $n"));
$totalpages = ceil($totalrows / 50);

if($uzpage >= $totalpages){ 
$nxt_icon = "";
$nxt_data = "chtoto";
}else{ 
$nxt_icon = "➡️";
$nxt_num = $page+1;
$nxt_data = "page=$n=$nxt_num";
}

$keyboard2[]=[['text'=>"$nxt_icon",'callback_data'=>"$nxt_data"]];
$keyboard2[]=[['text'=>"🔙Ortga",'callback_data'=>"ortga"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
$trailer=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM trailers WHERE anime_id = $n"));
if($trailer['type']=="video"){
$method='sendVideo';
$param='video';
}elseif($trailer['type']=="photo"){
$method='sendPhoto';
$param='photo';
}
bot("$method",[
'chat_id'=>$chat_id,
"$param"=>$trailer['video_id'],
'caption'=>"".$rew['title']."
──────────────────────
➤ Mavsum: ".$rew['season']."
➤ Qismlar: ".$rew['episodes']."
➥ Sifat: 720p | 1080p
──────────────────────",
'parse_mode'=>'html',
'reply_markup'=>$kb
]);
}else{
accl($qid,"⚠️ Anime topilmadi, yoki o‘chrib yuborilgan!",1);
}
}

if(stripos($data,"page=")!==false){
$anime_id = explode("=",$data)[1];
$page = explode("=",$data)[2];
$ofset = ($page - 1) * 50;
$anime = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
$totalrows = mysqli_num_rows(mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $anime_id"));
$totalpages = ceil($totalrows / 50);

$res=mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $anime_id LIMIT $ofset,50");
while($row=mysqli_fetch_assoc($res)){
$keys[]=['text'=>"".$row['episode']."",'callback_data'=>"watchAnime=$anime_id=".$row['episode'].""];
}
$e=array_chunk($keys,5);

if($page <= 1){
$bck_icon = "";
$bck_data = "chtoto";
}else{
$bck_icon = "⬅️";
$bck_num = $page-1;
$bck_data = "page=$anime_id=$bck_num";
}

if($page >= $totalpages){ 
$nxt_icon = "";
$nxt_data = "chtoto";
}else{ 
$nxt_icon = "➡️";
$nxt_num = $page+1;
$nxt_data = "page=$anime_id=$nxt_num";
}

$e[]=[['text'=>"$bck_icon",'callback_data'=>$bck_data],['text'=>"$nxt_icon",'callback_data'=>$nxt_data]];
$keyboard = json_encode(['inline_keyboard'=>$e]);

bot('editMessageCaption',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'caption'=>"".$anime['title']."
──────────────────────
➤ Mavsum: ".$anime['season']."
➤ Qismlar: ".$anime['episodes']."
➥ Sifat: 720p | 1080p
──────────────────────",
'parse_mode'=>"HTML",
'reply_markup'=>$keyboard
]);
}

// By: t.me/sadiyuz


if(mb_stripos($data,"watchAnime=")!==false){
$anime_id=explode("=",$data)[1];
$epnum=explode("=",$data)[2];
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $anime_id AND episode = $epnum"));
$rew=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$chat_id,
'video'=>$row['video_id'],
'caption'=>"".$rew['title']." [$epnum-qism]",
]);
}

//<----- Keyingi qism ------>

if($text=="🔴 Shorts bo'lim" and joinchat($cid)==1){
$rew=mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM trailers WHERE `type` = 'video' ORDER BY RAND() LIMIT 1"));
if($rew){
$anime_id=$rew['anime_id'];
$info=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$cid,
'video'=>$rew['video_id'],
'caption'=>"<b>🎬Anime nomi:</b> ".$info['title']."
<b>📑 Qoʻshimcha:</b> <i>".$info['about']."</i>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🖥Tomosha qilish",'callback_data'=>"loadAnime=$anime_id"],['text'=>"Keyingi➡️",'callback_data'=>"nextShorts"]]
]
])
]);
}else{
sms($cid,"<b>⚠️ Shortslar mavjud emas</b>",null);
}
}

if($data=="nextShorts"){
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM trailers ORDER BY RAND() LIMIT 1"));
if($row){
del();
$anime_id=$row['anime_id'];
$info=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$chat_id,
'video'=>$row['video_id'],
'caption'=>"<b>🎬Anime nomi:</b> ".$info['title']."
<b>📑 Qoʻshimcha:</b> <i>".$info['about']."</i>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🖥Tomosha qilish",'callback_data'=>"loadAnime=$anime_id"],['text'=>"Keyingi➡️",'callback_data'=>"nextShorts"]]
]
])
]);
}else{
accl($qid,"⚠️ Shortslar qolmadi !",1);
}
}

if($text=="📚 Qo'llanma" and joinchat($cid)==1){
sms($cid,"<b>📚 AniMacUz botini ishlatish bo'yicha qo'llanma : </b>

🔎 Anime izlash - Botda mavjud bo'lgan animelarni qidirish uchun ishlatiladi. 
🔴 Shorts bo'lim - Botda yuklangan animelarni qisqacha edit yoki trellerini ko'rish uchun ishlatiladi. 
💸Reklama va Homiylik - bot adminlari bilan reklama yoki homiylik yuzasidan aloqaga chiqish.

<b>ID :</b> <code>$cid</code>",null);
}

if($text=="💵 Reklama va Homiylik" and joinchat($cid)==1){
sms($cid,"<b>💰 Reklama va va homiylik uchun:</b> @Muxammad_o7",null);
}

//<----- Admin Panel ------>

if((strtolower($text)=="/admin" or $text=="🔙Ortga") and staff($cid)==1){
sms($cid,"✅Admin panelga hush kelibsiz !",$panel);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid");
exit;
}

if($data=="panel" and staff($chat_id)==1){
del();
sms($chat_id,"✅Admin panelga hush kelibsiz !",$panel);
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $chat_id");
exit;
}

if(staff($cid)==1 and !in_array($cid,$owners)){
if($text=="💬Xabar yuborish" or $text=="🔐Majburiy a'zo" or $text=="👔Staff qo'shish"){
sms($cid,"<b>⚠️Ushbu bo'limdan foydalanish ega uchun ruxsat berilgan !</b>",null);
exit;
}
}

if($text=="🆕Anime qo'shish" and staff($cid)==1){
delkey();
sms($cid,"<b>🆕Yangi anime uchun til tanlang :</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"🇺🇿Ozbekcha",'callback_data'=>"newanimelang=uz"]],
[['text'=>"🇷🇺Ruscha",'callback_data'=>"newanimelang=ru"]],
[['text'=>"🇺🇸Inglizcha",'callback_data'=>"newanimelang=en"]],
[['text'=>"🇯🇵Yaponcha",'callback_data'=>"newanimelang=jp"]],
[['text'=>"🔙Ortga",'callback_data'=>"panel"]],
]]));
}
// By: t.me/sadiyuz

if(mb_stripos($data,"newanimelang=")!==false){
$til=explode("=",$data)[1];
del();
$adds=json_decode(get("adds.json"),1);
$adds['lang']=$til;
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
sms($chat_id,"<b>🧿Anime uchun Treller yuboring !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_treller' WHERE id = $chat_id");
}

if($step=="anime_treller" and staff($cid)==1){
if(isset($message->video)){
$file_id=$message->video->file_id;
$adds=json_decode(get("adds.json"),1);
$adds['trailer']=$file_id;
$adds['trailer_type']="video";
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
}elseif(isset($message->photo)){
$file_id=$message->photo[count($message->photo)-1]->file_id;
$adds=json_decode(get("adds.json"),1);
$adds['trailer']=$file_id;
$adds['trailer_type']="photo";
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
}
sms($cid,"<b>🏷Anime nomini yuboring !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_title' WHERE id = $cid");
}

if($step=="anime_title" and staff($cid)==1){
if(isset($text)){
$text=$connect->real_escape_string($text);
$adds=json_decode(get("adds.json"),1);
$adds['title']=$text;
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
sms($cid,"<b>🌀Anime mavsumini kiriting !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_season' WHERE id = $cid");
}
}

if($step=="anime_season" and staff($cid)==1){
if(is_numeric($text)){
$text=$connect->real_escape_string($text);
$adds=json_decode(get("adds.json"),1);
$adds['season']=$text;
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
sms($cid,"<b>📄Anime haqida qisqacha matn kiriting !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_about' WHERE id = $cid");
}else{
sms($cid,"<b>❗Faqat raqamlardan foydalaning.</b>",null);
}
}

if($step=="anime_about" and staff($cid)==1){
if(isset($text)){
$text=$connect->real_escape_string($text);
$adds=json_decode(get("adds.json"),1);
$adds['about']=$text;
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
sms($cid,"<b>📑Anime janrlarini kiriting:</b>

Namuna: Ekshen,Sarguzasht,Drama...",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_genre' WHERE id = $cid");
}
}

if($step=="anime_genre" and staff($cid)==1){
if(isset($text)){
$text=$connect->real_escape_string($text);
$adds=json_decode(get("adds.json"),1);
$adds['genre']=$text;
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
sms($cid,"<b>#️⃣Anime uchun teg kiriting !</b>

Namuna: Gojo,Naruto,Kakashi,Saitama,Luffy",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_tegs' WHERE id = $cid");
}
}
// By: t.me/sadiyuz

if($step=="anime_tegs" and staff($cid)==1){
if(isset($text)){
$text=$connect->real_escape_string($text);
$adds=json_decode(get("adds.json"),1);
$adds['tegs']=$text;
put("adds.json",json_encode($adds,JSON_PRETTY_PRINT));
sms($cid,"<b>🎙Anime ovoz beruvchisini kiriting !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'anime_voter' WHERE id = $cid");
}
}

if($step=="anime_voter" and staff($cid)==1){
if(isset($text)){
$voter=$connect->real_escape_string($text);
$adds=json_decode(get("adds.json"));
$sql="INSERT INTO `animes`(`title`,`season`,`genre`,`voter`,`episodes`,`films`,`lang`,`tegs`,`status`,`about`,`views`) VALUES ('$adds->title','$adds->season','$adds->genre','$voter','0','0','$adds->lang','$adds->tegs','not completed','$adds->about','0')";
if($connect->query($sql)==true){
$anime_id=$connect->insert_id;
$connect->query("INSERT INTO `trailers`(`anime_id`,`video_id`,`type`) VALUES ('$anime_id','$adds->trailer','$adds->trailer_type')");
sms($cid,"<b>✅Anime muvaffaqiyatli qo'shildi !</b>",$panel);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
sms($cid,"⚠️ Xatolik!

<code>$connect->error</code>",$panel);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}
}
}





if($text=="➕Seriya qo'shish" and staff($cid)==1){
sms($cid,"<b>➕Seriya qo'shilishi kerak bo'lgan anime nomini yuboring !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'add-episode' WHERE id = $cid");
}

if($step=="add-episode" and staff($cid)==1){
$text = mysqli_real_escape_string($connect,$text);
$rew = mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%'");
$c = mysqli_num_rows($rew);
if($c > 1){
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>$a['title'],'callback_data'=>"edit-episode=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$keyboard2[]=[['text'=>"❌ Yopish",'callback_data'=>"close"]];
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
sms($cid,"<i>$text</i> <b>qidiruvi boʻyicha natijalar: $c ta</b>",$kb);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%' LIMIT 1"));
$anime_id=$row['id'];
if($row){
sms($cid,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
if($row['lang']=="uz"){
$til="🇺🇿Ozbekcha";
}elseif($row['lang']=="ru"){
$til="🇷🇺Ruscha";
}elseif($row['lang']=="en"){
$til="🇺🇸Inglizcha";
}elseif($row['lang']=="jp"){
$til="🇯🇵Yaponcha";
}
if($row['status']=="completed"){
$status="Tugallangan";
}else{
$status="Davom etmoqda";
}
$trailer=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM trailers WHERE anime_id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$cid,
'video'=>$trailer['video_id'],
]);
sms($cid,"🆔 : $anime_id
--------------------
<b>🏷Nomi :</b> ".$row['title']."
<b>📑Janri :</b> ".$row['genre']."
<b>🎙Ovoz beruvchi :</b> ".$row['voter']."
--------------------
<b>🎞Seriyalar soni :</b> ".$row['episodes']."
<b>🎥Filmlar soni :</b> ".$row['films']."
--------------------
<b>💬Tili :</b> $til
--------------------
<b>#️⃣Teg :</b> ".$row['tegs']."
<b>📉Status :</b> $status
<b>👁‍🗨Ko'rishlar :</b> ".$row['views']."",json_encode([
'inline_keyboard'=>[
[['text'=>"🎞Seriya qo'shish",'callback_data'=>"new-episode=$anime_id"]],
[['text'=>"🟢Tugallash",'callback_data'=>"complete=$anime_id"]],
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
sms($cid,"⚠️ Anime topilmadi, yoki o‘chrib yuborilgan!",null);
}
}
}

if(mb_stripos($data,"new-episode=")!==false){
$anime_id=explode("=",$data)[1];
del();
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
$count=mysqli_num_rows(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id"));
sms($chat_id,"❗<b><i>".$row['title']."</i> animesi uchun <i>".($count+1)." - seriyani</i> yuboring !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = '$data' WHERE id = $chat_id");
}

if(mb_stripos($step,"new-episode=")!==false){
if(isset($message->video)){ 
$anime_id=explode("=",$step)[1];
$file_id=$message->video->file_id;
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
$count=mysqli_num_rows(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id"));
$count=$count+1;
$date=date('H:i:s d.m.Y');
mysqli_query($connect,"INSERT INTO videos(anime_id,episode,video_id,`date`) VALUES ('$anime_id','$count','$file_id','$date')");
mysqli_query($connect,"UPDATE animes SET episodes = $count WHERE id = $anime_id");
sms($cid,"✅<b><i>".$row['title']."</i> animesi uchun <i>$count - seriya</i> qabul qilindi !</b>

⚠️<b><i>".($count+1)."-seriyani</i> yuboring:</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
}else{
sms($cid,"<b>Video yuboring</b>",null);
}
}

if(mb_stripos($data,"complete=")!==false){
$anime_id=explode("=",$data)[1];
mysqli_query($connect,"UPDATE animes SET `status` = 'completed' WHERE id = $anime_id");
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
edit($chat_id,$message_id,"🆔 : $anime_id
--------------------
<b>🏷Nomi :</b> ".$row['title']."
<b>📑Janri :</b> ".$row['genre']."
<b>🎙Ovoz beruvchi :</b> ".$row['voter']."
--------------------
<b>🎞Seriyalar soni :</b> ".$row['episodes']."
<b>🎥Filmlar soni :</b> ".$row['films']."
--------------------
<b>💬Tili :</b> $til
--------------------
<b>#️⃣Teg :</b> ".$row['tegs']."
<b>📉Status :</b> Tugallangan
<b>👁‍🗨Ko'rishlar :</b> ".$row['views']."",json_encode([
'inline_keyboard'=>[
[['text'=>"🟡Davom ettirish",'callback_data'=>"notcomplete=$anime_id"]],
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
}
if(mb_stripos($data,"notcomplete=")!==false){
$anime_id=explode("=",$data)[1];
mysqli_query($connect,"UPDATE animes SET `status` = 'not completed' WHERE id = $anime_id");
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
edit($chat_id,$message_id,"🆔 : $anime_id
--------------------
<b>🏷Nomi :</b> ".$row['title']."
<b>📑Janri :</b> ".$row['genre']."
<b>🎙Ovoz beruvchi :</b> ".$row['voter']."
--------------------
<b>🎞Seriyalar soni :</b> ".$row['episodes']."
<b>🎥Filmlar soni :</b> ".$row['films']."
--------------------
<b>💬Tili :</b> $til
--------------------
<b>#️⃣Teg :</b> ".$row['tegs']."
<b>📉Status :</b> Davom etmoqda
<b>👁‍🗨Ko'rishlar :</b> ".$row['views']."",json_encode([
'inline_keyboard'=>[
[['text'=>"🎞Seriya qo'shish",'callback_data'=>"new-episode=$anime_id"]],
[['text'=>"🟢Tugallash",'callback_data'=>"complete=$anime_id"]],
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
}

if($text=="✏️Anime tahrirlash" and staff($cid)==1){
sms($cid,"<b>✏️Tahrirlash kerak bo'lgan anime nomini yuboring !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'edit-anime' WHERE id = $cid");
}
// By: t.me/sadiyuz

if($step=="edit-anime" and staff($cid)==1){
$text = mysqli_real_escape_string($connect,$text);
$rew = mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%'");
$c = mysqli_num_rows($rew);
if($c > 1){
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>$a['title'],'callback_data'=>"edit-anime=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$keyboard2[]=[['text'=>"❌ Yopish",'callback_data'=>"close"]];
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
sms($cid,"<i>$text</i> <b>qidiruvi boʻyicha natijalar: $c ta</b>",$kb);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%' LIMIT 1"));
$anime_id=$row['id'];
if($row){
sms($cid,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
if($row['lang']=="uz"){
$til="🇺🇿Ozbekcha";
}elseif($row['lang']=="ru"){
$til="🇷🇺Ruscha";
}elseif($row['lang']=="en"){
$til="🇺🇸Inglizcha";
}elseif($row['lang']=="jp"){
$til="🇯🇵Yaponcha";
}
if($row['status']=="completed"){
$status="🟢Tugallangan";
}else{
$status="🟡Davom etmoqda";
}
$trailer=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM trailers WHERE anime_id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$cid,
'video'=>$trailer['video_id'],
]);
sms($cid,"🆔 : $anime_id
--------------------
<b>🏷Nomi :</b> ".$row['title']."
<b>📑Janri :</b> ".$row['genre']."
<b>🎙Ovoz beruvchi :</b> ".$row['voter']."
--------------------
<b>🎞Seriyalar soni :</b> ".$row['episodes']."
<b>🎥Filmlar soni :</b> ".$row['films']."
--------------------
<b>💬Tili :</b> $til
--------------------
<b>#️⃣Teg :</b> ".$row['tegs']."
<b>📉Status :</b> $status
<b>👁‍🗨Ko'rishlar :</b> ".$row['views']."",json_encode([
'inline_keyboard'=>[
[['text'=>"📝Haqidani tahrirlash",'callback_data'=>"editAnime=$anime_id=about"]],
[['text'=>"🏷Nomini tahrirlash",'callback_data'=>"editAnime=$anime_id=title"]],
[['text'=>"📑Janrini tahrirlash",'callback_data'=>"editAnime=$anime_id=genre"]],
[['text'=>"🎙Fandubni tahrirlash",'callback_data'=>"editAnime=$anime_id=voter"]],
[['text'=>"💬Tilini tahrirlash",'callback_data'=>"editAnime=$anime_id=lang"]],
[['text'=>"️#️⃣Tegni tahrirlash",'callback_data'=>"editAnime=$anime_id=tegs"]],
[['text'=>"🗑️Animeni o'chirish",'callback_data'=>"deleteAnime=$anime_id"]],
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
sms($cid,"⚠️ Anime topilmadi, yoki o‘chrib yuborilgan!",null);
}
}
}

if(mb_stripos($data,"edit-anime=")!==false){
$anime_id=explode("=",$data)[1];
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
if($row){
del();
sms($cid,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
if($row['lang']=="uz"){
$til="🇺🇿Ozbekcha";
}elseif($row['lang']=="ru"){
$til="🇷🇺Ruscha";
}elseif($row['lang']=="en"){
$til="🇺🇸Inglizcha";
}elseif($row['lang']=="jp"){
$til="🇯🇵Yaponcha";
}
if($row['status']=="completed"){
$status="🟢Tugallangan";
}else{
$status="🟡Davom etmoqda";
}
$trailer=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM trailers WHERE anime_id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$chat_id,
'video'=>$trailer['video_id'],
]);
sms($chat_id,"🆔 : $anime_id
--------------------
<b>🏷Nomi :</b> ".$row['title']."
<b>📑Janri :</b> ".$row['genre']."
<b>🎙Ovoz beruvchi :</b> ".$row['voter']."
--------------------
<b>🎞Seriyalar soni :</b> ".$row['episodes']."
<b>🎥Filmlar soni :</b> ".$row['films']."
--------------------
<b>💬Tili :</b> $til
--------------------
<b>#️⃣Teg :</b> ".$row['tegs']."
<b>📉Status :</b> $status
<b>👁‍🗨Ko'rishlar :</b> ".$row['views']."",json_encode([
'inline_keyboard'=>[
[['text'=>"📝Haqidani tahrirlash",'callback_data'=>"editAnime=$anime_id=about"]],
[['text'=>"🏷Nomini tahrirlash",'callback_data'=>"editAnime=$anime_id=title"]],
[['text'=>"📑Janrini tahrirlash",'callback_data'=>"editAnime=$anime_id=genre"]],
[['text'=>"🎙Fandubni tahrirlash",'callback_data'=>"editAnime=$anime_id=voter"]],
[['text'=>"💬Tilini tahrirlash",'callback_data'=>"editAnime=$anime_id=lang"]],
[['text'=>"️#️⃣Tegni tahrirlash",'callback_data'=>"editAnime=$anime_id=tegs"]],
[['text'=>"🗑️Animeni o'chirish",'callback_data'=>"deleteAnime=$anime_id"]],
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
accl($qid,"⚠️ Anime topilmadi, yoki o‘chrib yuborilgan!",1);
}
}

if(mb_stripos($data,"editAnime=")!==false){
$anime_id=explode("=",$data)[1];
$tip=explode("=",$data)[2];
del();
if($tip=="about"){
$matn="📝Ushbu anime uchun yangi ma'lumot yuboring !";
}elseif($tip=="title"){
$matn="🏷Ushbu anime uchun yangi Nom yuboring !";
// By: t.me/sadiyuz
}elseif($tip=="genre"){
$matn="📑Ushbu anime uchun yangi Janr yuboring !";
}elseif($tip=="voter"){
$matn="🎙Ushbu anime uchun yangi Ovoz beruvchi yuboring !";
}elseif($tip=="lang"){
$matn="💬Ushbu anime uchun yangi Til tanlang !";
}elseif($tip=="tegs"){
$matn="#️⃣Ushbu anime uchun yangi Teg yuboring !";
}
sms($chat_id,$matn,$aort);
mysqli_query($connect,"UPDATE users SET step = '$data' WHERE id = $chat_id");
}

if(mb_stripos($step,"editAnime=")!==false){
$anime_id=explode("=",$step)[1];
$tip=explode("=",$step)[2];
delkey();
sms($cid,"<b>⚠️Tahrirlashni tasdiqlaysizmi?</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"✅Ha",'callback_data'=>"editing-$anime_id-$tip"],['text'=>"❌Yo'q",'callback_data'=>"panel"]]
]]));
$text=$connect->real_escape_string($text);
mysqli_query($connect,"UPDATE users SET step = '$text' WHERE id = $cid");
}


if(mb_stripos($data,"editing-")!==false){
$anime_id=explode("-",$data)[1];
$tip=explode("-",$data)[2];
mysqli_query($connect,"UPDATE animes SET `$tip` = '$step' WHERE id = $anime_id");
if($tip=="about"){
$matn="✅Anime Haqidasi yangilandi!";
}elseif($tip=="title"){
$matn="✅Anime Nomi yangilandi!";
// By: t.me/sadiyuz
}elseif($tip=="genre"){
$matn="✅Anime Janr yangilandi!";
}elseif($tip=="voter"){
$matn="✅Anime Ovoz beruvchisi yangilandi!";
}elseif($tip=="tegs"){
$matn="✅Anime Teg yangilandi!";
}
accl($qid,$matn,1);
del();
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
del();
sms($cid,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
if($row['lang']=="uz"){
$til="🇺🇿Ozbekcha";
}elseif($row['lang']=="ru"){
$til="🇷🇺Ruscha";
}elseif($row['lang']=="en"){
$til="🇺🇸Inglizcha";
}elseif($row['lang']=="jp"){
$til="🇯🇵Yaponcha";
}
if($row['status']=="completed"){
$status="🟢Tugallangan";
}else{
$status="🟡Davom etmoqda";
}
$trailer=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM trailers WHERE anime_id = $anime_id"));
bot('sendVideo',[
'chat_id'=>$chat_id,
'video'=>$trailer['video_id'],
]);
sms($chat_id,"🆔 : $anime_id
--------------------
<b>🏷Nomi :</b> ".$row['title']."
<b>📑Janri :</b> ".$row['genre']."
<b>🎙Ovoz beruvchi :</b> ".$row['voter']."
--------------------
<b>🎞Seriyalar soni :</b> ".$row['episodes']."
<b>🎥Filmlar soni :</b> ".$row['films']."
--------------------
<b>💬Tili :</b> $til
--------------------
<b>#️⃣Teg :</b> ".$row['tegs']."
<b>📉Status :</b> $status
<b>👁‍🗨Ko'rishlar :</b> ".$row['views']."",json_encode([
'inline_keyboard'=>[
[['text'=>"📝Haqidani tahrirlash",'callback_data'=>"editAnime=$anime_id=about"]],
[['text'=>"🏷Nomini tahrirlash",'callback_data'=>"editAnime=$anime_id=title"]],
[['text'=>"📑Janrini tahrirlash",'callback_data'=>"editAnime=$anime_id=genre"]],
[['text'=>"🎙Fandubni tahrirlash",'callback_data'=>"editAnime=$anime_id=voter"]],
[['text'=>"💬Tilini tahrirlash",'callback_data'=>"editAnime=$anime_id=lang"]],
[['text'=>"️#️⃣Tegni tahrirlash",'callback_data'=>"editAnime=$anime_id=tegs"]],
[['text'=>"🗑️Animeni o'chirish",'callback_data'=>"deleteAnime=$anime_id"]],
[['text'=>"🔙Chiqish",'callback_data'=>"panel"]]
]]));
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}

if(mb_stripos($data,"deleteAnime=")!==false){
$anime_id=explode("=",$data)[1];
mysqli_query($connect,"DELETE FROM `trailers` WHERE anime_id = $anime_id");
mysqli_query($connect,"DELETE FROM `animes` WHERE id = $anime_id");
accl($qid,"✅Anime o'chirildi!",1);
del();
delkey();
sms($chat_id,"/admin ezing✅",null);
}


if($text=="✏️Seriya tahrirlash" and staff($cid)==1){
sms($cid,"<b>📝Seriyasi tahrirlanishi kerak bo'lgan anime nomini yuboring !</b>",$aort);
// By: t.me/sadiyuz
mysqli_query($connect,"UPDATE users SET step = 'edit-episode' WHERE id = $cid");
}

if($step=="edit-episode" and staff($cid)==1){
$text = mysqli_real_escape_string($connect,$text);
$rew = mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%'");
$c = mysqli_num_rows($rew);
if($c > 1){
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>$a['title'],'callback_data'=>"edit-episode=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$keyboard2[]=[['text'=>"❌ Yopish",'callback_data'=>"close"]];
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
sms($cid,"<i>$text</i> <b>qidiruvi boʻyicha natijalar: $c ta</b>",$kb);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE title LIKE '%$text%' OR tegs LIKE '%$text%' LIMIT 1"));
$anime_id=$row['id'];
if($row){
sms($cid,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
$episodeInfo=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id AND episode = 1"));
bot('sendVideo',[
'chat_id'=>$cid,
'video'=>$episodeInfo['video_id'],
]);
$res=mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id");
while($m=mysqli_fetch_assoc($res)){
if($m['episode']=="1"){
$uz[]=['text'=>"[ ".$m['episode']." ]",'callback_data'=>"chooseEp=".$m['episode']."=$anime_id"];
}else{
$uz[]=['text'=>$m['episode'],'callback_data'=>"selectEp=".$m['episode']."=$anime_id"];
}
}
$keyboard2=array_chunk($uz,10);
$keyboard2[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
sms($cid,"🆔 : $anime_id
<b>🏷Nomi :</b> ".$row['title']."
--------------------
🆔 : ".$episodeInfo['id']."
<b>💾Qism : </b> 1",$kb);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}else{
// By: t.me/sadiyuz
sms($cid,"⚠️ Anime topilmadi, yoki o‘chrib yuborilgan!",null);
}
}
}

if(mb_stripos($data,"edit-episode=")!==false){
$anime_id=explode("=",$data)[1];
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
if($row){
del();
sms($chat_id,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
$episodeInfo=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id AND episode = 1"));
bot('sendVideo',[
'chat_id'=>$chat_id,
'video'=>$episodeInfo['video_id'],
]);
$res=mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id");
while($m=mysqli_fetch_assoc($res)){
if($m['episode']=="1"){
$uz[]=['text'=>"[ ".$m['episode']." ]",'callback_data'=>"chooseEp=".$m['episode']."=$anime_id"];
}else{
$uz[]=['text'=>$m['episode'],'callback_data'=>"selectEp=".$m['episode']."=$anime_id"];
}
}
$keyboard2=array_chunk($uz,10);
$keyboard2[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
sms($chat_id,"🆔 : $anime_id
<b>🏷Nomi :</b> ".$row['title']."
--------------------
🆔 : ".$episodeInfo['id']."
<b>💾Qism : </b> 1",$kb);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $chat_id");
}else{
accl($qid,"⚠️ Anime topilmadi, yoki o‘chrib yuborilgan!",1);
}
}

if(mb_stripos($data,"selectEp=")!==false){
$epcode=explode("=",$data)[1];
$anime_id=explode("=",$data)[2];
bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$message_id]);
bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$message_id-1]);
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
$episodeInfo=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id AND episode = $epcode"));
bot('sendVideo',[
'chat_id'=>$chat_id,
'video'=>$episodeInfo['video_id'],
]);
// By: t.me/sadiyuz
$res=mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id");
while($m=mysqli_fetch_assoc($res)){
if($m['episode']==$epcode){
$uz[]=['text'=>"[ ".$m['episode']." ]",'callback_data'=>"chooseEp=".$m['episode']."=$anime_id"];
}else{
$uz[]=['text'=>$m['episode'],'callback_data'=>"selectEp=".$m['episode']."=$anime_id"];
}
}
$keyboard2=array_chunk($uz,10);
$keyboard2[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
sms($chat_id,"🆔 : $anime_id
<b>🏷Nomi :</b> ".$row['title']."
--------------------
🆔 : ".$episodeInfo['id']."
<b>💾Qism : </b> $epcode",$kb);
}

if(mb_stripos($data,"chooseEp=")!==false){
$epcode=explode("=",$data)[1];
$anime_id=explode("=",$data)[2];
del();bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$message_id-1]);
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
$episodeInfo=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id AND episode = $epcode"));
$lastEp=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = 1 ORDER BY episode DESC LIMIT 1"));
bot('sendVideo',['chat_id'=>$chat_id,'video'=>$episodeInfo['video_id']]);
$uz[]=['text'=>"♻️Boshqa yuklash",'callback_data'=>"reupload=$epcode=$anime_id"];
if($epcode==$lastEp['episode']) $uz[]=['text'=>"🗑️O'chirish",'callback_data'=>"deleteEp=$epcode=$anime_id"];
$keyboard2=array_chunk($uz,2);
$keyboard2[]=[['text'=>"🔙Ortga",'callback_data'=>"selectEp=$epcode=$anime_id"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
sms($chat_id,"🆔 : $anime_id
<b>🏷Nomi :</b> ".$row['title']."
--------------------
🆔 : ".$episodeInfo['id']."
<b>💾Qism : </b> $epcode",$kb);
}

if(mb_stripos($data,"reupload=")!==false){
$epcode=explode("=",$data)[1];
$anime_id=explode("=",$data)[2];
del();bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$message_id-1]);

// By: t.me/sadiyuz
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
sms($chat_id,"<b>❗<i>".$row['title']."</i> animesining $epcode-seriyasi uchun boshqa seriya yuboring !</b>",$kb);
mysqli_query($connect,"UPDATE users SET step = '$data' WHERE id = $chat_id");
}

if(mb_stripos($step,"reupload=")!==false){
if(isset($message->video)){
$epcode=explode("=",$step)[1];
$anime_id=explode("=",$step)[2];
$file_id=$message->video->file_id;
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
mysqli_query($connect,"UPDATE `videos` SET video_id = '$file_id' WHERE anime_id = $anime_id AND episode = $epcode");
sms($cid,"<b>🟢<i>".$row['title']."</i> animesini $epcode - seriyasi yangisiga almashtirildi !</b>",null);
bot('sendVideo',['chat_id'=>$cid,'video'=>$file_id]);

$res=mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id");
while($m=mysqli_fetch_assoc($res)){
if($m['episode']==$epcode){
$uz[]=['text'=>"[ ".$m['episode']." ]",'callback_data'=>"chooseEp=".$m['episode']."=$anime_id"];
}else{
$uz[]=['text'=>$m['episode'],'callback_data'=>"selectEp=".$m['episode']."=$anime_id"];
}
}
$keyboard2=array_chunk($uz,10);
$keyboard2[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
sms($cid,"🆔 : $anime_id
<b>🏷Nomi :</b> ".$row['title']."
--------------------
🆔 : ".$episodeInfo['id']."
<b>💾Qism : </b> $epcode",$kb);
}
}

if(mb_stripos($data,"deleteEp=")!==false){
$epcode=explode("=",$data)[1];
$anime_id=explode("=",$data)[2];
mysqli_query($connect,"DELETE FROM `videos` WHERE anime_id = $anime_id AND episode = $epcode");
$row=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `animes` WHERE id = $anime_id"));
accl($qid,"✅".$row['title']." animesini $epcode - seriyasi o'chirildi!",1);
del();bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$message_id-1]);

$episodeInfo=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id ORDER BY `episode` DESC LIMIT 1"));
$epid=$episodeInfo['episode'];
bot('sendVideo',['chat_id'=>$chat_id,'video'=>$episodeInfo['video_id']]);

$res=mysqli_query($connect,"SELECT * FROM `videos` WHERE anime_id = $anime_id");
while($m=mysqli_fetch_assoc($res)){
if($m['episode']==$epid){
$uz[]=['text'=>"[ ".$m['episode']." ]",'callback_data'=>"chooseEp=".$m['episode']."=$anime_id"];
}else{
$uz[]=['text'=>$m['episode'],'callback_data'=>"selectEp=".$m['episode']."=$anime_id"];
}
}
$keyboard2=array_chunk($uz,10);
$keyboard2[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
sms($chat_id,"🆔 : $anime_id
<b>🏷Nomi :</b> ".$row['title']."
--------------------
🆔 : ".$episodeInfo['id']."
<b>💾Qism : </b> $epid",$kb);
}

if($text=="📬 Post tayyorlash" and staff($cid)==1){
// By: t.me/sadiyuz
sms($cid,"<b>🆔 Anime ID raqamini kiriting:</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'createPost=code' WHERE id = $cid");
exit();
}

if(stripos($step,"createPost=")!==false){
$ty=explode("=",$step)[1];
if($ty=="code" and is_numeric($text)){
$adds['code']=$text;
put("adds.json",json_encode($adds,128));
sms($cid,"<b>🖼️Rasm yoki video yuboring !</b>",null);
mysqli_query($connect,"UPDATE users SET step = 'createPost=thumb' WHERE id = $cid");
exit;
}elseif($ty=="thumb"){
if(isset($message->video) or isset($message->photo)){
$adds=json_decode(get("adds.json"),1);
if(isset($message->photo)){
$adds['file_id']=$file;
$adds['file_type']="photo";
}elseif(isset($message->video)){
$adds['file_id']=$message->video->file_id;
$adds['file_type']="video";
}
put("adds.json",json_encode($adds,128));
sms($cid,"<b>📒Post uchun tavsif yuboring !</b>",json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"Kiritishni hohlamayman"]],
[['text'=>"🔙Ortga"]]
]]));
mysqli_query($connect,"UPDATE users SET step = 'createPost=desc' WHERE id = $cid");
exit;
}
}elseif($ty=="desc" and isset($text)){
$adds=json_decode(get("adds.json"),1);
$adds['caption']=$text;
put("adds.json",json_encode($adds,128));
sms($cid,"<b>🎛️Tugmalarni yuboring na'munadagidek !</b>

<b><u>NA'MUNA:</u></b>
HAVOLA NOMI => HAVOLA MANZILI",$aort);
mysqli_query($connect,"UPDATE users SET step = 'createPost=button' WHERE id = $cid");
exit;
}elseif($ty=="button" and isset($text)){
$buttons=explode("\n",$text);
$array=[];
foreach($buttons as $button){
$exp=explode(" => ",$button);
$array[]=['text'=>"$exp[0]",'url'=>"$exp[1]"];
}
$e=array_chunk($array,1);
$k=json_encode(['inline_keyboard'=>$e]);

$adds=json_decode(get("adds.json"),1);
$capt=$adds['caption'] == "Kiritishni hohlamayman" ? "" : $adds['caption'];
$channels = ['-1002539737687', '@AniMacUz'];

foreach ($channels as $channel) {
    if ($adds['file_type'] == "photo") {
        bot('sendPhoto', [
            'chat_id' => $channel, // By: t.me/sadiyuz
            'photo' => $adds['file_id'],
            'caption' => $capt,
            'parse_mode' => "html",
            'reply_markup' => $k
        ]);
    } elseif ($adds['file_type'] == "video") {
        bot('sendVideo', [
            'chat_id' => $channel,
            'video' => $adds['file_id'],
            'caption' => $capt,
            'parse_mode' => "html",
            'reply_markup' => $k
        ]);
    }
}

sms($cid,"<b>✅Post tayyorlandi,  ".implode(", ", $channels)."a yuborildi!</b>",$panel);
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
unlink("adds.json");
exit;
}
}

// By: t.me/sadiyuz

if($text=="📊Statik ma'lumotlar" and staff($cid)==1){
$stat=mysqli_num_rows(mysqli_query($connect,"SELECT*FROM users"));
$anistat=mysqli_num_rows(mysqli_query($connect,"SELECT*FROM animes"));
sms($cid,"<b>📊AniMacUz botining statistikasi :</b>
----------------------------------------
<b>👥Foydalanuvchilar soni : $stat
🖥Animelar soni : $anistat</b>
----------------------------------------",null);
}

if ($text == "💬Xabar yuborish" && in_array($cid, $owners)) {
    sms($cid, "<b>💬Botdagi foydalanuvchilarga yuborish uchun xabar kiriting !</b>", $aort);
    mysqli_query($connect, "UPDATE users SET step = 'send' WHERE id = $cid");
}

if ($step == "send" && in_array($cid, $owners)) {
    sms($cid, "<b>⚠️Ushbu xabarni yuborishni tasdiqlaysizmi?</b>", json_encode([
        'inline_keyboard' => [
            [['text' => "✅Ha", 'callback_data' => "send=$mid"], ['text' => "❌Yo'q", 'callback_data' => "panel"]],
        ]
    ]));
    mysqli_query($connect, "UPDATE users SET step = '0' WHERE id = $cid");
}

if (mb_stripos($data, "send=") !== false) {
    $message_id_to_send = explode("=", $data)[1];
    del();
    sms($cid, "🔃 Xabar yuborilmoqda", $panel);

    $sql = "SELECT * FROM `users`";
    $res = mysqli_query($connect, $sql);

    while ($a = mysqli_fetch_assoc($res)) {
        $response = bot('copyMessage', [
            'chat_id' => $a['id'],
            'from_chat_id' => $chat_id,
            'message_id' => $message_id_to_send
        ]);

        if (!$response->ok) {
            file_put_contents("error_log.txt", "Error sending to " . $a['id'] . ": " . $response->description . "\n", FILE_APPEND);
        }
        usleep(100000);
    }

    sms($chat_id, "✅Xabar yuborish tugallandi", $panel);
}

// By: t.me/sadiyuz


if($text=="👤Alohida xabar" and staff($cid)==1){
sms($cid,"<b>💬Xabar yuborish uchun foydalanuvchi IDsini kiriting:</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'sendToUser' WHERE id = $cid");
}

if($step=="sendToUser" and staff($cid)==1){
$getchat=bot('getChat',['chat_id'=>$text]);
$user_id=$getchat->result->id;
$user_name=$getchat->result->username;
$rew=mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM users WHERE id = $user_id"));
if($rew){
if(staff($user_id)==1)$adstat="ADMIN";
else $adstat="USER";
sms($cid,"✅Foydalanuvchi topildi :

🆔: $user_id
👤Username : @$user_name
---------------
💬Til: uz
---------------
📌Adminlik : $adstat",null);
sms($cid,"<b>❗️Ushbu foydalanuvchiga yuborish uchun xabar kiriting !</b>",null);
mysqli_query($connect,"UPDATE users SET step = 'sendToUser-$user_id' WHERE id = $cid");
}else{
sms($cid,"⚠️ Foydalanuvchi mavjud emas",null);
}
}

if(mb_stripos($step,"sendToUser-")!==false){
$user_id=explode('-',$step)[1];
sms($cid,"<b>⚠️Ushbu xabarni yuborishni tasdiqlaysizmi?</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"✅Ha",'callback_data'=>"sendToUser=$user_id=$mid"],['text'=>"❌Yo'q",'callback_data'=>"panel"]],
]]));
mysqli_query($connect,"UPDATE users SET step = '0' WHERE id = $cid");
}

if(mb_stripos($data,"sendToUser=")!==false){
$user_id=explode("=",$data)[1];
$mess_id=explode("=",$data)[2];
del();
$ok=bot('forwardMessage',['chat_id'=>$user_id,'from_chat_id'=>$chat_id,'message_id'=>$mess_id])->ok;
if($ok){
sms($chat_id,"<b>✅Xabar yuborildi !</b>",$panel);
}else{
sms($chat_id,"<b>❌Xabar yuborilmadi !</b>",$panel);
}
}

if($text=="🔐Majburiy a'zo" and in_array($cid,$owners)){
$res=$connect->query("SELECT * FROM `channels`");
delkey();
if($res->num_rows > 0){
$res=mysqli_query($connect,"SELECT * FROM `channels`");
while($a=mysqli_fetch_assoc($res)){
if($a['channelID']=="none"){
$bir[]=['text'=>"".$a['name']."",'callback_data'=>"remove-channel=".$a['id']];
}else{
$getchat=json_decode(get("https://api.telegram.org/bot".BOT_TOKEN."/getchat?chat_id=".$a['channelID']),1);
$bir[]=['text'=>"".$getchat['result']['title']."",'callback_data'=>"remove-channel=".$a['id']];
}
}
$keyboard=array_chunk($bir,1);
$keyboard[]=[['text'=>"➕",'callback_data'=>"add-channel"],['text'=>"🔗 Silka qo‘shish",'callback_data'=>"add_link"]];
$keyboard[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard]);
$km=sms($cid,"<b>🔒Sponsorlikdan chiqarilishi</b> kerak bo'lgan Kanalni tanlang yoki <b>➕ orqali yana qo'shing</b>",$kb);
}else{
sms($cid,"<b>🔒Majburiy a'zo qo'shish</b> uchun majburiy a'zo qo'shilishi kerak bo'lgan <b>kanaldan istalgan habarni botga ulashing</b>
❗️Bo't siz <b>majburiy a'zo qilmoqchi bo'lgan kanalda</b> admin etib tayinlangan bo'lishi zarur !",$aort);
mysqli_query($connect,"UPDATE users SET step = 'add-channel' WHERE id = $cid");
}
}

if($data=="add-channel" and in_array($chat_id,$owners)){
del();
sms($chat_id,"<b>🔒Majburiy a'zo qo'shish</b> uchun majburiy a'zo qo'shilishi kerak bo'lgan <b>kanaldan istalgan habarni botga ulashing</b>
❗️Bo't siz <b>majburiy a'zo qilmoqchi bo'lgan kanalda</b> admin etib tayinlangan bo'lishi zarur !",$aort);
mysqli_query($connect,"UPDATE users SET step = 'add-channel' WHERE id = $chat_id");
}

if($step==="add-channel" and in_array($cid,$owners)){
if(isset($message->forward_origin)){
$channelID=$message->forward_origin->chat->id;
$idbot=bot('getMe')->result->id;
$stat=bot('getChatMember',['chat_id'=>$channelID,'user_id'=>$idbot])->result->status;
if($stat=="administrator"){
sms($cid,"<b>⚠️ Kanal qanday turda qo'shmoqchisiz ?</b>",inline([
[['text'=>"⭐️ Ommaviy",'callback_data'=>"add_channel=public"],['text'=>"❓Zayafka",'callback_data'=>"add_channel=joinrequest"]],
[['text'=>"🔗 Qo'shish shaxsiy link bilan.",'callback_data'=>"add_channel=optionalLink"]]
]));
$json=json_decode(get('chann.json'),1);
$json['channel_id']=$channelID;
put('chann.json',json_encode($json,JSON_PRETTY_PRINT));
mysqli_query($connect,"UPDATE users SET step='none' WHERE id = $cid");
}else{
sms($cid,"<b>⚠️ Bo'tni admin etib tayinlangan bo'lishi zarur !</b>",$aort);
}
}
}

if((stripos($data,"add_channel=")!==false and in_array($chat_id,$owners))){
$ty = explode("=",$data)[1];
if($ty == "public"){
del();
$json=json_decode(get('chann.json'),1);
$info = bot('getChat',['chat_id'=>$json['channel_id']]);
$title = $info->result->title;
$title = $connect->real_escape_string($title);
$link = isset($info->result->username) ? "https://t.me/".$info->result->username : $info->result->invite_link;
$connect->query("INSERT INTO `channels` (`channelID`,`name`,`link`,`type`) VALUES ('".$json['channel_id']."','$title','$link','public');");
sms($chat_id,"✅ <a href='$link'>Kanal</a> <b>qo'shildi</b>",$panel);
unlink('chann.json');
}elseif($ty == "joinrequest"){
edit($chat_id,$message_id,"❓ Havolani avtomatik olsinmi ?",inline([
[['text'=>"✅ Ha",'callback_data'=>"add_channel=request=okay"],['text'=>"❌ Yo'q",'callback_data'=>"add_channel=request=nope"]]
]));
}elseif($ty == "request"){
del();
$json=json_decode(get('chann.json'),1);
if(explode("=",$data)[2]=="okay"){
$info = bot('getChat',['chat_id'=>$json['channel_id']]);
$title = $info->result->title;
$title = $connect->real_escape_string($title);
$link = bot('createChatInviteLink',['chat_id'=>$json['channel_id'],'creates_join_request'=>true])->result->invite_link;
$connect->query("INSERT INTO `channels` (`channelID`,`name`,`link`,`type`) VALUES ('".$json['channel_id']."','$title','$link','request');");
sms($chat_id,"✅ <b>Kanal so'rov qabul qilish uchun tayyor !</b>",$panel);
unlink('chann.json');
}elseif(explode("=",$data)[2]=="nope"){
sms($chat_id,"🖇 Endi havolani yuboring !",$back);
mysqli_query($connect,"UPDATE users SET step='join_request' WHERE id = $chat_id");
}
}elseif($ty=="optionalLink"){
del();
sms($chat_id,"🔗 <b>Havolani yuboring.</b>",$aort);
mysqli_query($connect,"UPDATE users SET step = 'add-channel2' WHERE id = $chat_id");
}
// By: t.me/sadiyuz
}

if($step == 'add-channel2' && in_array($cid,$owners)){
if(stripos($text,"https://t.me/")!==false){
$json=json_decode(get('chann.json'),1);
$info = bot('getChat',['chat_id'=>$json['channel_id']]);
$title = $info->result->title;
$title = $connect->real_escape_string($title);
$connect->query("INSERT INTO `channels` (`channelID`,`name`,`link`,`type`) VALUES ('".$json['channel_id']."','$title','$text','public');");
sms($cid,"✅ <a href='$text'>Kanal</a> <b>qo'shildi</b>",$panel);
unlink('chann.json');
mysqli_query($connect,"UPDATE users SET step = 'none' WHERE id = $cid");
exit();
}
}
// By: t.me/sadiyuz

if($step == "join_request" and in_array($cid,$owners)){
if(stripos($text,"https://t.me/")!==false){
$json = json_decode(get('chann.json'),1);
$info = bot('getChat',['chat_id'=>$json['channel_id']]);
$title = $info->result->title;
$title = $connect->real_escape_string($title);
$connect->query("INSERT INTO `channels` (`channelID`,`name`,`link`,`type`) VALUES ('".$json['channel_id']."','$title','$link','public');");
sms($cid,"✅ <a href='$link'>Kanal</a> <b>qo'shildi</b>",$panel);
mysqli_query($connect,"UPDATE users SET step='none' WHERE id = $chat_id");
unlink('chann.json');
}
}

if($data=="add_link" and in_array($chat_id,$owners)){
del();
sms($chat_id,"📋 Silka uchun nom yuboring !",$aort);
mysqli_query($connect,"UPDATE users SET step='$data' WHERE id = $chat_id");
}

if($step=="add_link" and in_array($cid,$owners)){
sms($cid,"🔗 Silkani yuboring !",$aort);
$json=json_decode(get('chann.json'),1);
$json['link_name']=$text;
put('chann.json',json_encode($json,JSON_PRETTY_PRINT));
mysqli_query($connect,"UPDATE users SET step='add_link2' WHERE id = $cid");
}

if($step=="add_link2" and in_array($cid,$owners)){
$json=json_decode(get('chann.json'),1);
$connect->query("INSERT INTO `channels` (`channelID`,`name`,`link`,`type`) VALUES ('none','".$json['link_name']."','$text','optional');");
sms($cid,"✅ <a href='$text'>Silka</a> <b>qo'shildi</b>",$panel);
mysqli_query($connect,"UPDATE users SET step='none' WHERE id = $cid");
unlink('chann.json');
}

if(mb_stripos($data,"remove-channel=")!==false){
$id=explode("=",$data)[1];
del();
mysqli_query($connect,"DELETE FROM channels WHERE id = $id");
sms($chat_id,"<b>✅Ushbu kanal</b> Majburiy a'zodan <b>o'chirib yuborildi</b> !",$panel);
}

function inline($r=[]){
return json_encode([
'inline_keyboard'=>$r
]);
}
// By: t.me/sadiyuz

if($text=="👔Staff qo'shish" and in_array($cid,$owners)){
$res=$connect->query("SELECT * FROM `admins`");
if($res->num_rows > 0){
delkey();
$res=mysqli_query($connect,"SELECT * FROM `admins`");
while($a=mysqli_fetch_assoc($res)){
$getchat=json_decode(get("https://api.telegram.org/bot".BOT_TOKEN."/getchat?chat_id=".$a['user_id']),1);
$bir[]=['text'=>"".$getchat['result']['first_name']."",'callback_data'=>"remove-staff=".$a['id']];
}
$keyboard=array_chunk($bir,1);
$keyboard[]=[['text'=>"➕",'callback_data'=>"add-staff"]];
$keyboard[]=[['text'=>"🔙Chiqish",'callback_data'=>"panel"]];
$kb=json_encode(['inline_keyboard'=>$keyboard]);
$km=sms($cid,"<b>👔Staff user o'chirish</b> kerak bo'lgan foydanuvchini tanlang yoki <b>➕ orqali yana qo'shing</b>",$kb);
}else{
sms($cid,"<b>👔Staff User</b> qo'shish uchun <b>Foydalanuvchi ID sini</b> yuboring !",$aort);
mysqli_query($connect,"UPDATE users SET step = 'add-staff' WHERE id = $cid");
}
}

if($data=="add-staff" and in_array($chat_id,$owners)){
del();
sms($chat_id,"<b>👔Staff User</b> qo'shish uchun <b>Foydalanuvchi ID sini</b> yuboring !",$aort);
mysqli_query($connect,"UPDATE users SET step = 'add-staff' WHERE id = $chat_id");
}

if($step==="add-staff" and in_array($cid,$owners)){
if(isset($text)){
mysqli_query($connect,"INSERT INTO admins(user_id) VALUES ('$text')");
sms($text,"👔Siz <b>Admin tomonidan Staff</b> dajarasiga ko'tarildingiz ! /admin orqali <b>Staff panelga o'tishingiz mumkin !</b>",null);
sms($cid,"<b>✅Ushbu user Staff darajasiga ko'ratildi !</b>",$aort);
mysqli_query($connect,"UPDATE users SET step='none' WHERE id = $cid");
}
}

if(mb_stripos($data,"remove-staff=")!==false){
$id=explode("=",$data)[1];
del();
mysqli_query($connect,"DELETE FROM admins WHERE id = $id");
sms($chat_id,"<b>✅Ushbu kanal</b> Majburiy a'zodan <b>o'chirib yuborildi</b> !",$panel);
}

//<----- Admin Panel ------>

if(mb_stripos($text,"/start ")!==false and joinchat($cid)==1){
$anime_id=str_replace('/start ','',$text);

viewAnime($cid,$anime_id);
$rew=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animes WHERE id = $anime_id"));
if($rew){
$a=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `anime_datas` WHERE `id` = $anime_id ORDER BY `qism` ASC LIMIT 1"));
sms($cid,"<b>✅Anime topildi</b>",json_encode(['remove_keyboard'=>true]));
if($rew['lang']=="uz"){
$til="o‘zbek";
}elseif($rew['lang']=="ru"){
$til="rus";
}elseif($rew['lang']=="en"){
$til="ingliz";
}elseif($rew['lang']=="jp"){
$til="yapon";
}
$res=mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $anime_id LIMIT 0,50");
while($row=mysqli_fetch_assoc($res)){
$keys[]=['text'=>"".$row['episode']."",'callback_data'=>"watchAnime=$anime_id=".$row['episode'].""];
}

$keyboard2=array_chunk($keys,5);

$totalrows = mysqli_num_rows(mysqli_query($connect,"SELECT * FROM videos WHERE anime_id = $anime_id"));
$totalpages = ceil($totalrows / 50);


if(1 >= $totalpages){ 
$nxt_icon = "";
$nxt_data = "chtoto";
}else{ 
$nxt_icon = "➡️";
$nxt_data = "page=$anime_id=2";
}

$keyboard2[]=[['text'=>"$nxt_icon",'callback_data'=>"$nxt_data"]];
$keyboard2[]=[['text'=>"🔙Ortga",'callback_data'=>"ortga"]];
$kb=json_encode(['inline_keyboard'=>$keyboard2]);
$trailer=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM trailers WHERE anime_id = $anime_id"));
// By: t.me/sadiyuz
if($trailer['type']=="video"){
$method='sendVideo';
$param='video';
}elseif($trailer['type']=="photo"){
$method='sendPhoto';
$param='photo';
}
bot("$method",[
'chat_id'=>$cid,
"$param"=>$trailer['video_id'],
'caption'=>"".$rew['title']."
──────────────────────
➤ Mavsum: ".$rew['season']."
➤ Qismlar: ".$rew['episodes']."
➥ Sifat: 720p | 1080p
──────────────────────",
'parse_mode'=>'html',
'reply_markup'=>$kb
]);
}else{
sms($cid,"<b>Assalomu alaykum!
Botimizga xush kelibsiz ✌️</b>",$menu);
}
}

// By: t.me/sadiyuz
