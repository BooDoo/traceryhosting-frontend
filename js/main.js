$('#tracery').bind('input propertychange', function() {
	generate();
	unsaved = true;
	changeSaveButtonColour();
});

$('#frequency').change(function() {
	unsaved = true;
	changeSaveButtonColour();
});


$('#public_source').change(function() {
	unsaved = true;
	changeSaveButtonColour();
});

$('#is_sensitive').change(function() {
	unsaved = true;
	changeSaveButtonColour();
});

$('#visibility').change(function() {
	unsaved = true;
	changeSaveButtonColour();
});


$('#does_replies').change(function() {
	unsaved = true;
	changeSaveButtonColour();

	console.log($('#does_replies').val() );
	if ($('#does_replies').val() == 0)
	{
		$('#reply_rules_container').addClass('hidden');
		$("#reply_rules").expanding('destroy');
		$("#test_mention").expanding('destroy');
	}
	else
	{
		$('#reply_rules_container').removeClass('hidden');
		$("#reply_rules").expanding();
		$("#test_mention").expanding();

		generate_reply();
	}
});

if ($("#reply_rules").is(":visible"))
{
	$("#reply_rules").expanding();
	$("#test_mention").expanding();
}


$('#reply_rules').bind('input propertychange', function() {
	unsaved = true;
	changeSaveButtonColour();

	generate_reply();
});



$('#test_mention').bind('input propertychange', function() {

	generate_reply();
});

$( "#refresh-generated-reply" ).bind( "click", function() {
  generate_reply();
});



$( "#refresh-generated-status" ).bind( "click", function() {
  generate();
});


$(window).bind('beforeunload', function(e){
	if (unsaved) return "This page is asking you to confirm that you want to leave - data you have entered may not be saved";
});



$(window).load(function() {
	if (tracery.createGrammar)
	{
		generate();
	}
	else
	{
		_.defer(generate, 500);
	}
  
});

var valid = true;
var replyrules_valid = true;

nl2br = function (str, is_xhtml) {   
	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
};

// Returns a "tagObject" like: {img: `https://imgur.com/21324567`} or {cut: `uspol`}
var prepareTag = function(tag) {
	const knownTags = ["img", "svg", "cut", "alt", "hide", "show", "public", "unlisted", "private", "direct"];
	let match = tag.match(/^\{((?:img|svg|cut|alt) |hide|show|public|unlisted|private|direct)(.*)\}/);
	if ( match && match[1] && _.includes(knownTags, match[1].trim()) ) {
		let tagType = match[1].trim();
		let tagContent = match[2];

		const unescapeOpenBracket = /\\{/g;
		const unescapeCloseBracket = /\\}/g;
		tagContent = tagContent.replace(unescapeOpenBracket, "{");
		tagContent = tagContent.replace(unescapeCloseBracket, "}");

		toReturn = {};
		toReturn[tagType] = tagContent;
		return toReturn;

	} else {
		console.error(`No known action for ${tag.split(' ')[0]}, ignoring`);
	}
}

// this is much more complex than i thought it would be
// but this function will find our image tags 
// full credit to BooDooPerson - https://twitter.com/BooDooPerson/status/683450163608817664
// Reverse the string, check with our fucked up regex, return null or reverse matches back
var matchBrackets = function(text) {
  
  // simple utility function
  function reverseString(s) {
	return s.split('').reverse().join('');
  }

  // this is an inverstion of the natural order for this RegEx:
  var bracketsRe = /(\}(?!\\)(.+?)\{(?!\\))/g;

  text = reverseString(text);
  var matches = text.match(bracketsRe);
  if(matches === null) {
	return null;
  }
  else {
	return matches.map(reverseString).reverse().map(prepareTag);
  }
};


//see matchBrackets for why this is like this
function removeBrackets (text) {
  
  // simple utility function
  var reverseString = function(s) {
	return s.split('').reverse().join('');
  }

  // this is an inverstion of the natural order for this RegEx:
  var bracketsRe = /(\}(?!\\)(.+?)\{(?!\\))/g;

  text = reverseString(text);
  return reverseString(text.replace(bracketsRe, ""));
}


var validate_reply_rules = function()
{
	var string = $('textarea#reply_rules').val();
	try
	{
		var parsed = jQuery.parseJSON(string); 

		var regexes_valid = _.all(parsed, function(val, key, index)
		{
			try
			{
				var regex = new RegExp(key);
				return true;
			}
			catch (e)
			{
				$('#replyrules-validator').removeClass('hidden').html("RegExp parse error for rule " + key + ":  <pre>" + _.escape(e) + "</pre>");
				return false;
			}
		})

		if (regexes_valid)
		{
			$('#replyrules-validator').addClass('hidden').text("Parsed successfully");
			replyrules_valid = true;
		}
		else
		{
			replyrules_valid = true;
		}

	}
	catch (e) {


		try {
			var result = jsonlint.parse(string);
			if (result) {
				//valid via jsonlint?!
				$('#replyrules-validator').removeClass('hidden').text("Unknown JSON parse error: " + _.escape(e));
			}
		} catch(e) {
			$('#replyrules-validator').removeClass('hidden').html("JSON parse error:  <pre>" + _.escape(e) + "</pre>");
		}

		replyrules_valid = false;
	}


	$('#save-button').toggleClass('disabled', !(valid && replyrules_valid));
};

var generate_reply = function()
{
	validate_reply_rules();
	if (replyrules_valid && processedGrammar != null)
	{
		let validatorId = 'reply-validator';
		let statusId = 'generated-reply';
		let mediaId = 'reply-media';
		let spoilerId = 'reply-media-spoiler';
		let postButtonId = 'post-generated-status';

		var mention = $('textarea#test_mention').val();
		let username = _.last(url.split('/'));

		const VISIBILITIES = ["public", "unlisted", "private", "direct"];
		let visibility = document.getElementById('visibility').value;

		if (mention.indexOf(username) == -1) //if we're not @ed
		{
			$(`#${statusId}`).html(`<i>Not mentioned</i><div id="${mediaId}"></div>`);
		}
		else
		{
			var reply_rules = jQuery.parseJSON($('textarea#reply_rules').val()); //could be quicker if we only parse this once
			var origin = _.find(reply_rules, function(origin,rule) {
				return (new RegExp(rule)).test(mention);
			});
			var reply = processedGrammar.flatten(origin);

			let meta_tags = matchBrackets(reply);
			let just_text_status = removeBrackets(reply);
			$(`#${statusId}`).html(nl2br(_.escape(just_text_status)) + `<div id="${mediaId}"></div>`);

			if (reply == "")
			{
				$(`#${statusId}`).html(`<i>No reply</i><div id="${mediaId}"></div>`);
			}
			else
			{
				$(`#${statusId}`).html(nl2br(_.escape(just_text_status)) + `<div id="${mediaId}"></div>`);
				$(`#${statusId}`).append(`
				<button type="button" id="${spoilerId}">
					 <span id="${spoilerId}__warning">Sensitive content</span>
					 <span id="${spoilerId}__trigger">Click to view</span>
				 </button>
				`);
			}


			if (twttr.txt.getTweetLength(just_text_status) > 500)
			{
				$(`#${statusId}`).addClass('too-long');
			}
			else
			{
				$(`#${statusId}`).removeClass('too-long');
			}

			if (!_.isEmpty(meta_tags))
			{

				let medias = [];
				let cw_label = null;
				let alt_tags = [];
				let meta_visibility = null;
				let hide_media = null;
				let show_media = null;

				cw_label = _.find(meta_tags, tagObject=> _.has(tagObject, "cut")); // we take the first CUT, or leave it undefined
				if (cw_label) { cw_label = _.escape(cw_label['cut']) };
				alt_tags = meta_tags.filter(tagObject=> _.has(tagObject, "alt")); // we take all ALT tags, in sequence
				medias = meta_tags.filter(tagObject=>_(["img","svg"]).includes(Object.keys(tagObject)[0])); // we take all IMG or SVG tags, in sequence

				meta_visibility = _.find(VISIBILITIES.reverse(), vis=>
							meta_tags.find(tagObject=>
								tagObject.hasOwnProperty(vis)
						  ));
				visibility = meta_visibility || visibility;


				hide_media = _.find(meta_tags, tagObject=>_.has(tagObject, "hide")); // undefined or [{hide: ""}...]
				show_media = _.find(meta_tags, tagObject=>_.has(tagObject, "show")); // undefined or [{show: ""}...]

				if (hide_media && show_media) {
					hide_media = true; // both given explicitly, prefer to HIDE
					show_media = false;
				}
				else if (show_media) {
					hide_media = false;
				}
				else if (hide_media) {
					show_media = false;
				}
				else {
					// nether show nor hide given explicitly, look at standard inheritance
					if (document.getElementById('is_sensitive')) {
						hide_media = hide_media || parseInt(document.getElementById('is_sensitive').value, 10);
					}
					hide_media = hide_media || !_.isEmpty(cw_label);
				}

				if ( hide_media ) {
					$(`#${mediaId}`).addClass('sensitive').addClass('hidden');
				}
				else {
					$(`#${mediaId}`).removeClass('sensitive').removeClass('hidden');
				}

				if (!_.isEmpty(cw_label)) {
					let generated_status = document.getElementById(statusId);
					generated_status.innerHTML = `
									<span class="spoiler_text">${cw_label}</span>
									<details>
										<summary>
											<a class="btn btn-default btn-cut"><span></span></a>
										</summary>

										${generated_status.innerHTML}
									</details>`;
				}

				_.each(medias, (tagObject, index)=> {
					let tagType, tagContent, description;
					[tagType, tagContent] = _.pairs(tagObject)[0];

					description = alt_tags[_.min([index, alt_tags.length-1])]; // pair media content with alt tag (if present)
					if (_.has(description, "alt")) { description = description.alt; } // or fallback to undefined
					description = _.escape(description);

					if (tagType === "svg") {
						var parser = new DOMParser();
						var doc = parser.parseFromString(tagContent, "image/svg+xml");

						validateSVG(doc, tagContent);

						$(`#${mediaId}`).append(`<div class="svg-media" aria-label="${description}" title="${description}">${tagContent}</div>`);

					}

					else if (tagType === "img") {
						$(`#${mediaId}`).append(`<div class="svg-media"> <img alt="${description}" title="${description}" src="${tagContent}"></div>`);
					}
				});
			};

			// set visibilty glyph for reply
			document.getElementById('generated-reply-visibility').className = "btn disabled glyphicon glyphicon-" + visibility;
			document.getElementById('generated-reply-visibility').title = "Reply would post with '" + visibility + "' visibility';
		}

	}
	else
	{
		$('#generated-reply').html("---").attr('disabled','disabled').addClass('disabled');
	}
};

generate_reply = _.throttle(generate_reply, 500);


var status; //global so we can see it when we press the Post! button
var processedGrammar; //global so it can be used for replies
var generate = function()
{
	let validatorId = 'tracery-validator';
	let statusId = 'generated-status';
	let mediaId = 'status-media';
	let spoilerId = 'media-spoiler';
	let postButtonId = 'post-generated-status';

	processedGrammar = null;
	var string = $('textarea#tracery').val();
	try{
		var parsed = jQuery.parseJSON(string);
		try
		{
			const VISIBILITIES = ["public", "unlisted", "private", "direct"];
			let visibility = document.getElementById("visibility").value;

			$(`#${validatorId}`).addClass('hidden').text("Parsed successfully");


			processedGrammar = tracery.createGrammar(parsed);

			processedGrammar.addModifiers(tracery.baseEngModifiers);
			status = processedGrammar.flatten("#origin#");

			var meta_tags = matchBrackets(status);
			var just_text_status = removeBrackets(status);
			$(`#${statusId}`).html(nl2br(_.escape(just_text_status)) + `<div id="${mediaId}"></div>`);
			$(`#${statusId}`).append(`
			<button type="button" id="${spoilerId}">
				 <span id="${spoilerId}__warning">Sensitive content</span>
				 <span id="${spoilerId}__trigger">Click to view</span>
			 </button>
			`);

			if (twttr.txt.getTweetLength(just_text_status) > 500)
			{
				$(`#${statusId}`).addClass('too-long');

				$(`#${postButtonId}`).attr('disabled','disabled').addClass('disabled');
			}
			else
			{
				$(`#${statusId}`).removeClass('too-long');
				$(`#${postButtonId}`).removeAttr('disabled').removeClass('disabled');
			}
 

			if (!_.isEmpty(meta_tags))
			{

				console.dir(meta_tags);

				let medias = [];
				let cw_label = null;
				let alt_tags = [];
				let meta_visibility = null;
				let hide_media = null;
				let show_media = null;

				cw_label = _.find(meta_tags, tagObject=> _.has(tagObject, "cut")); // we take the first CUT, or leave it undefined
				if (cw_label) { cw_label = _.escape(cw_label['cut']) };
				alt_tags = meta_tags.filter(tagObject=> _.has(tagObject, "alt")); // we take all ALT tags, in sequence
				medias = meta_tags.filter(tagObject=>_(["img","svg"]).includes(Object.keys(tagObject)[0])); // we take all IMG or SVG tags, in sequence

				meta_visibility = _.find(VISIBILITIES.reverse(), vis=>
							meta_tags.find(tagObject=>
								tagObject.hasOwnProperty(vis)
						  ));
				visibility = meta_visibility || visibility;

				hide_media = _.find(meta_tags, tagObject=>_.has(tagObject, "hide")); // undefined or [{hide: ""}...]
				show_media = _.find(meta_tags, tagObject=>_.has(tagObject, "show")); // undefined or [{show: ""}...]

				if (hide_media && show_media) {
					hide_media = true; // both given explicitly, prefer to HIDE
					show_media = false;
				}
				else if (show_media) {
					hide_media = false;
				}
				else if (hide_media) {
					show_media = false;
				}
				else {
					// nether show nor hide given explicitly, look at standard inheritance
					if (document.getElementById('is_sensitive')) {
						hide_media = hide_media || parseInt(document.getElementById('is_sensitive').value, 10);
					}
					hide_media = hide_media || !_.isEmpty(cw_label);
				}

				if ( hide_media ) {
					$(`#${mediaId}`).addClass('sensitive').addClass('hidden');
				}
				else {
					$(`#${mediaId}`).removeClass('sensitive').removeClass('hidden');
				}

				if (!_.isEmpty(cw_label)) {
					let generated_status = document.getElementById(statusId);
					generated_status.innerHTML = `
									<span class="spoiler_text">${cw_label}</span>
									<details>
										<summary>
											<a class="btn btn-default btn-cut"><span></span></a>
										</summary>

										${generated_status.innerHTML}
									</details>`;
				}

				_.each(medias, (tagObject, index)=> {
					let tagType, tagContent, description;
					[tagType, tagContent] = _.pairs(tagObject)[0];

					description = alt_tags[_.min([index, alt_tags.length-1])]; // pair media content with alt tag (if present)
					if (_.has(description, "alt")) { description = description.alt; } // or fallback to undefined
					description = _.escape(description);

					if (tagType === "svg") {
						var parser = new DOMParser();
						var doc = parser.parseFromString(tagContent, "image/svg+xml");

						validateSVG(doc, tagContent);

						$(`#${mediaId}`).append(`<div class="svg-media" aria-label="${description}" title="${description}">${tagContent}</div>`);

					}

					else if (tagType === "img") {
						$(`#${mediaId}`).append(`<div class="svg-media"> <img alt="${description}" title="${description}" src="${tagContent}"></div>`);
					}
				});
			};

			// Set visibility indication on Post button
			document.getElementById("post-generated-status").title = "Post status with '" + visibility + "' visibility";
			document.getElementById("generated-status-visibility").className = "glyphicon glyphicon-" + visibility;

			valid = true;
		}
		catch (e)
		{

			$(`#${validatorId}`).removeClass('hidden').text("Tracery parse error: " + _.escape(e));
			valid = false;
		}
	}
	catch (e) {

		try {
			var result = jsonlint.parse(string);
			if (result) {
				//valid via jsonlint?!
				$(`#${validatorId}`).removeClass('hidden').text("Unknown JSON parse error: " + _.escape(e));
			}
		} catch(e) {
			$(`#${validatorId}`).removeClass('hidden').html("JSON parse error:  <pre>" + _.escape(e) + "</pre>");
		}

		valid = false;
	}

	$('#save-button').toggleClass('disabled', !(valid && replyrules_valid));

	if ($("#reply_rules").is(":visible"))
	{
		generate_reply();
	} 
};

generate = _.throttle(generate, 500);

var unsaved = false;

$( window ).unload(function() {
	if (unsaved)
	{
		return "Unsaved changes";
	}
});

var validateSVG = function(doc, actualSVG)
{
	var parser = new DOMParser();
	var parsererrorNS = parser.parseFromString('INVALID', 'text/xml').getElementsByTagName("parsererror")[0].namespaceURI;


	if (doc.documentElement.getAttribute("width") === null)
	{
		$('#tracery-validator').removeClass('hidden').html("SVG element must specify a <code>width</code>");
	}
	if (doc.documentElement.getAttribute("height") === null)
	{
		$('#tracery-validator').removeClass('hidden').html("SVG element must specify a <code>height</code>");
	}

	if(doc.getElementsByTagNameNS(parsererrorNS, 'parsererror').length > 0) {

	var excerpt = "";
	//chrome
	var bracketsRe = /line (\d+) at column (\d+)/;
	var errorText = new XMLSerializer().serializeToString(doc.documentElement);
	var matches = errorText.match(bracketsRe);
	if(matches !== null) {
	var line = matches[1];
	var col = matches[2];
		excerpt = excerptAtLineCol(actualSVG, matches[1] - 1, matches[2] - 1, 1);
	}

	


		$('#tracery-validator').removeClass('hidden').html("SVG parsing error<br><pre>" + _.escape(excerpt) + "</pre><span class=\"parsererror\">" + nl2br(doc.getElementsByTagName('parsererror')[0].innerHTML) + "</span>");
	}

}

//from https://github.com/smallhelm/excerpt-at-line-col/blob/master/index.js

var excerptAtLineCol = function(text, line_n, col_n, n_surrounding_lines){
  n_surrounding_lines = n_surrounding_lines || 0;

  return text.split("\n").map(function(line, line_i){
	return {
	  line: line,
	  line_n: line_i
	};
  }).filter(function(l){
	return Math.abs(l.line_n - line_n) <= n_surrounding_lines;
  }).map(function(l){
	if(l.line_n !== line_n){
	  return l.line;
	}
	var col_position_whitespace = '';
	var j;
	for(j=0; j<Math.min(col_n, l.line.length); j++){
	  col_position_whitespace += l.line[j].replace(/[^\s]/g, " ");
	}
	return l.line + "\n" + col_position_whitespace + '^';
  }).join("\n");
};


var changeSaveButtonColour = function()
{
	if (unsaved) $('#save-button').removeClass('btn-default').addClass('btn-primary');
	else $('#save-button').removeClass('btn-primary').addClass('btn-default');
};

$('#post-generated-status').click(function()
{
	$.ajax({
	  url: "post.php",
	  method : "POST",
	  data : {"status": status},
	  dataType: "json"	  
	})
	  .done(function( data ) {
		if (data.hasOwnProperty('success') && data['success'])
		{

			$('#post-generated-status').attr('disabled','disabled').addClass('disabled');
			$('#tracery-validator').addClass('hidden');
		}
		else {
			$('#tracery-validator').removeClass('hidden').text("Failed to post: " + (data.hasOwnProperty('reason') && data['reason']));
		}
	  })
	  .fail( function( jqXHR, textStatus ) {
			$('#tracery-validator').removeClass('hidden').text("Failed to post: " + textStatus);
		});
});


$(window).bind('keydown', function(event) {
	if (event.ctrlKey || event.metaKey) {
		switch (String.fromCharCode(event.which).toLowerCase()) {
		case 's':
			event.preventDefault();
			save();
			break;
		}
	}
});


$( "#tracery-form" ).submit(function( event ) {
  event.preventDefault();
  save();
});

var save = function()
{
  if (valid && replyrules_valid)
  {
	var freq = $('#frequency').val();
	var tracery = $('#tracery').val();
	var public_source = $('#public_source').val();
	var is_sensitive = $('#is_sensitive').val();
	var visibility = $('#visibility').val();
	var does_replies = $('#does_replies').val();
	var reply_rules = $('#reply_rules').val();
	$.ajax({
	  url: "update.php",
	  method : "POST",
	  data : {"frequency": freq , "tracery" : tracery, "public_source" : public_source, "is_sensitive": is_sensitive, "visibility": visibility, "does_replies" : does_replies, "reply_rules" : reply_rules},
	  dataType: "json"
	})
	  .done(function( data ) {
		if (data.hasOwnProperty('success') && data['success'])
		{
			$('#tracery-validator').addClass('hidden');
			unsaved = false;
			changeSaveButtonColour();
		}
		else {
			$('#tracery-validator').removeClass('hidden').text("Failure uploading: " + (data.hasOwnProperty('reason') && data['reason']));
		}
	  }) 
	  .fail( function( jqXHR, textStatus ) {
			$('#tracery-validator').removeClass('hidden').text("Failure uploading: " + textStatus);
		});
	
  }
}

$(document).delegate('#tracery', 'keydown', function(e) {
  var keyCode = e.keyCode || e.which;

  if (keyCode == 9) {
	e.preventDefault();
	var start = $(this).get(0).selectionStart;
	var end = $(this).get(0).selectionEnd;

	// set textarea value to: text before caret + tab + text after caret
	$(this).val($(this).val().substring(0, start)
				+ "\t"
				+ $(this).val().substring(end));

	// put caret at right position again
	$(this).get(0).selectionStart =
	$(this).get(0).selectionEnd = start + 1;
  }
});


$(document).delegate('#reply_rules', 'keydown', function(e) {
  var keyCode = e.keyCode || e.which;

  if (keyCode == 9) {
	e.preventDefault();
	var start = $(this).get(0).selectionStart;
	var end = $(this).get(0).selectionEnd;

	// set textarea value to: text before caret + tab + text after caret
	$(this).val($(this).val().substring(0, start)
				+ "\t"
				+ $(this).val().substring(end));

	// put caret at right position again
	$(this).get(0).selectionStart =
	$(this).get(0).selectionEnd = start + 1;
  }
});

$('body').on('click', '#media-spoiler, #status-media.sensitive', ()=> {
	return $('#status-media').toggleClass('hidden');
});

$('body').on('click', '#reply-media-spoiler, #reply-media.sensitive', ()=> {
	return $('#reply-media').toggleClass('hidden');
});
