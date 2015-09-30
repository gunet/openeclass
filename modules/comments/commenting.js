function xmlhttpPost(strURL, action, rid, rtype, confirmmsg, cid) {

	if (action == 'delete') {
		proceed = confirm(confirmmsg);
	} else if (action == 'new') {
		if (document.getElementById('textarea-'+rid).value.trim().length == 0) {
			//empty string value
			proceed = false;
		} else {
			proceed = confirm(confirmmsg);
		}
	} else if (action == 'editSave') {
		if (document.getElementById('edit-textarea-'+cid).value.trim().length == 0) {
			//empty string value
			proceed = false;
		} else {
			proceed = confirm(confirmmsg);
		}
	} else {
		proceed = true;
	}
	
	if (proceed) {
	
	    var xmlHttpReq = false;
	
	    //Mozilla/Safari/IE7
	    if (window.XMLHttpRequest) {
	        xmlHttpReq = new XMLHttpRequest();
	    } else if (window.ActiveXObject) { //IE older versions
	        xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	
	    xmlHttpReq.open('POST', strURL, true);
	    xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	    xmlHttpReq.onreadystatechange = function() {
	        if (xmlHttpReq.readyState == 4 && xmlHttpReq.status == 200) {
	        	if (action == 'new') {
	        		response = JSON.parse(xmlHttpReq.responseText);
	        		if (response[0] == 'OK') {
	        			document.getElementById('textarea-'+rid).value = "";
	        			document.getElementById('commentsNum-'+rid).textContent = parseInt(document.getElementById('commentsNum-'+rid).textContent)+1;
	        			numDivs = document.getElementById('comments-'+rid).childNodes.length;
	        			var addedDiv = document.createElement('div');
	        			addedDiv.id = 'comment-'+response[2];
	        			addedDiv.className = 'comment';
	        			addedDiv.innerHTML = response[1]+response[3];
	        			document.getElementById('comments-'+rid).appendChild(addedDiv);
	        			setTimeout(function(){fade(document.getElementById('comment-'+response[2]).childNodes[0])}, 3000);
	        		} else if (response[0] == 'ERROR') {
	        			
	        		}
	        	} else if (action == 'delete') {
	        		response = JSON.parse(xmlHttpReq.responseText);
	        		if (response[0] == 'OK') {
	        			document.getElementById('comment-'+cid).innerHTML = response[1];
	        			document.getElementById('commentsNum-'+rid).textContent = parseInt(document.getElementById('commentsNum-'+rid).textContent)-1;
	        			setTimeout(function(){fade(document.getElementById('comment-'+cid).childNodes[0])}, 3000);
	        		} else if (response[0] == 'ERROR') {
	        			document.getElementById('comment-'+cid).innerHTML += response[1];
	        			setTimeout(function(){fade(document.getElementById('comment-'+cid).childNodes[0])}, 3000);
	        		}
	        	} else if (action == 'editLoad') {
	        		response = JSON.parse(xmlHttpReq.responseText);
	        		if (response[0] == 'OK') {
	        			document.getElementById('comment_content-'+cid).innerHTML = response[2];
	        		} else if (response[0] == 'ERROR') {
	        			document.getElementById('comment-'+cid).innerHTML += response[1];
	        			setTimeout(function(){fade(document.getElementById('comment-'+cid).childNodes[0])}, 3000);
	        		}
	        	} else if (action == 'editSave') {
	        		response = JSON.parse(xmlHttpReq.responseText);
	        		if (response[0] == 'OK') {
	        			document.getElementById('comment_content-'+cid).innerHTML = response[1]+response[2];
	        			setTimeout(function(){fade(document.getElementById('comment_content-'+cid).childNodes[0])}, 3000);
	        		} else if (response[0] == 'ERROR') {
	        			document.getElementById('comment-'+cid).innerHTML += response[1];
	        			setTimeout(function(){fade(document.getElementById('comment-'+cid).childNodes[0])}, 3000);
	        		}
	        	}
	        } 
	    }
	
	    if (action == 'new') {
	    	var commentText = encodeURIComponent(document.getElementById('textarea-'+rid).value);
	    	var params = 'commentText='+commentText+'&action='+action+'&rid='+rid+'&rtype='+rtype;
	    } else if (action == 'delete' || action == 'editLoad') {
	    	var params = 'action='+action+'&cid='+cid+'&rid='+rid+'&rtype='+rtype;
	    } else if (action == 'editSave') {
	    	var commentText = encodeURIComponent(document.getElementById('edit-textarea-'+cid).value);
	    	var params = 'commentText='+commentText+'&action='+action+'&cid='+cid+'&rid='+rid+'&rtype='+rtype;
	    }
	    
	    xmlHttpReq.send(params);
    
	}
}

function fade(element) {
    var op = 1;  // initial opacity
    var timer = setInterval(function () {
        if (op <= 0.1){
            clearInterval(timer);
            element.style.display = 'none';
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= op * 0.1;
    }, 50);
}
