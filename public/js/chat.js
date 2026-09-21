let conversationId=null;



const messages=document.getElementById('messages');

const input=document.getElementById('message');

const send=document.getElementById('send');





function addMessage(
text,
type
){

let div=document.createElement('div');


div.className =
"message "+type;


div.innerHTML=text;


messages.appendChild(div);


messages.scrollTop =
messages.scrollHeight;

}





send.onclick=function(){


let text=input.value.trim();


if(!text)
return;



addMessage(text,'user');


input.value="";



addMessage(
"AI is typing...",
"assistant"
);



fetch('/chat/send',
{

method:'POST',

headers:{

'Content-Type':'application/json',

'X-CSRF-TOKEN':
document
.querySelector(
'meta[name="csrf-token"]'
)
.content

},


body:JSON.stringify({

message:text,

conversation_id:
conversationId


})

})

.then(res=>res.json())

.then(data=>{


conversationId=
data.conversation_id;



messages.lastChild.remove();



addMessage(
data.reply,
'assistant'
);



speech(data.reply);


});


};







/*
Load old conversations
*/


document
.querySelectorAll('.conversation')
.forEach(item=>{


item.onclick=function(){


conversationId=this.dataset.id;



fetch(
'/chat/'+conversationId
)

.then(res=>res.json())

.then(data=>{


messages.innerHTML="";



data.messages.forEach(msg=>{


addMessage(
msg.message,
msg.role
);



});


});


};



});






/*
New Chat
*/


document
.getElementById('newChat')
.onclick=function(){


conversationId=null;

messages.innerHTML="";


};
