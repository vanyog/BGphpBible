//
//  This script was created
//  by Mircho Mirev
//  mo /mo@momche.net/
//
//	:: feel free to use it for non-commercial use BUT
//	:: if you want to use this code PLEASE send me a note
//	:: and please keep this disclaimer intact
//

//translation routines

//just the same as EN but added as a feature requested by Slavej
var cOffLang =
{
	sName : 'OFF',
	sDName : 'OFF', //display name
	sMap : '',
	sRData : ''
}

var cCyrPho =
{
	sName : 'Bulgarian Cyrillc Phonetic',
	sDName : 'PHO', //display name
	sMap : 'aAbBwWgGdDeEvVzZiIjJkKlLmMnNoOpPrRsStTuUfFhHcC`~[{]}yYxX\\|qQ',
	sRData : 'аАбБвВгГдДеЕжЖзЗиИйЙкКлЛмМнНоОпПрРсСтТуУфФхХцЦчЧшШщЩъЪьЬюЮяЯ'
}

var cCyrBds =
{
	sName : 'Bulgarian Cyrillc',
	sDName : 'BDS', //display name
	sMap : 'dD/?lLhHoOeEgGpPrRxXuU.>;:kKfFmM,<iIjJwWbBnN[{\'"tTyYcCaAzZsSvVqQ]}',
	sRData : 'аАбБвВгГдДеЕжЖзЗиИйЙкКлЛмМнНоОпПрРсСтТуУфФхХцЦчЧшШщЩъЪьЬюЮяЯэЭыЫ;§'
}


var cTranslator = 
{
	//change here to set the default language
	sGlobalLangID : cCyrPho.sDName,
	bHelp : true,
	hCurrentLang : '',
	hCurrentInput : null,
	aLanguages : [],
	bUseKeySwitch : true,
	bDisabled : false,
	onSwitchLang : null
}

cTranslator.registerLang = function( hLang )
{
	this.aLanguages[ hLang.sDName ] = hLang
}

//change here to exclude some of the available languages
cTranslator.registerLang( cOffLang )
cTranslator.registerLang( cCyrPho )
cTranslator.registerLang( cCyrBds )

cTranslator.switchLang = function( sLang )
{
	if( typeof ( this.aLanguages[ sLang ] ) != 'undefined' )
	{
		this.hCurrentLang = this.aLanguages[ sLang ]
	}
    if( this.onSwitchLang !== null )
	{
		this.onSwitchLang( sLang )
	}
	if( typeof CookieManager != 'undefined' )
	{
		CookieManager.setCookie( 'molang', sLang, 1 )
	}
}

cTranslator.getNextLang = function( sCL )
{
	var bFound = false
	for( sKey in this.aLanguages  )
	{
		if( bFound )
		{
			return sKey
		}
        if( sCL === sKey )
		{
			bFound = true
		}
	}
	if( bFound )
	{
		return cOffLang.sDName
	}
}

cTranslator.toggleLang = function( hElement )
{
	var sNewLangID = ''
    if( typeof hElement === 'undefined' || hElement === null )
	{
		sNewLangID = this.getNextLang( this.sGlobalLangID )
		this.sGlobalLangID = sNewLangID
	}
	else
	{
		var sLangAtt = hElement.getAttribute( 'MOLANG' )
        if( sLangAtt !== 'DEFAULT' )
		{
			sNewLangID = this.getNextLang( sLangAtt )
			hElement.setAttribute( 'MOLANG', sNewLangID )
		}
		else
		{
			sNewLangID = this.getNextLang( this.sGlobalLangID )
			this.sGlobalLangID = sNewLangID
		}
		hElement.focus()
	}
	this.switchLang( sNewLangID )
}

cTranslator.initLanguage = function( hEvent )
{
    if( hEvent === null ) hEvent = window.event
	hElement = ( hEvent.srcElement ) ? hEvent.srcElement : hEvent.originalTarget 
	cTranslator.hCurrentInput = hElement
	var sLangAtt = hElement.getAttribute( 'MOLANG' )
    if( sLangAtt !== 'DEFAULT' )
	{
		cTranslator.switchLang( hElement.getAttribute( 'MOLANG' ) )
	}
	else
	{
		cTranslator.switchLang( cTranslator.sGlobalLangID )
	}
	return true
}

cTranslator.processKey = function( hEvent )
{
	if( cTranslator.bDisabled )
	{
		return true;
	}
    if( hEvent === null ) hEvent = window.event
	hElement = ( hEvent.srcElement ) ? hEvent.srcElement : hEvent.originalTarget 
    var nCode = hEvent.keyCode ? hEvent.keyCode : hEvent.charCode ? hEvent.charCode : hEvent.which ? hEvent.which : 0;
    if( ( hEvent.charCode !== null ) && ( hEvent.charCode !== nCode ) )
	{
		return
	}
	var sCode = String.fromCharCode( nCode )
	var nPos = cTranslator.hCurrentLang.sMap.indexOf( sCode )
	if( nPos >= 0 && !hEvent.ctrlKey && !hEvent.altKey )
	{
		sRep = cTranslator.hCurrentLang.sRData.charAt( nPos )
		if( window.event ) //we have IE
		{
			window.event.keyCode = sRep.charCodeAt()
		}
		else //no we have some kind of moz
		{
			if( bw.ns5 && bw.mozVersion >= 1.7 )
			{
				var e = document.createEvent( 'KeyEvents' );
				e.initKeyEvent("keypress",true,true,null,false,false,false,false,0,sRep.charCodeAt())
				hElement.dispatchEvent(e);
			}
			else
			{
				var nScrollTop = hElement.scrollTop
				var nScrollLeft = hElement.scrollLeft
				var nScrollWidth = hElement.scrollWidth
				cTranslator.replaceSelection( hElement, sRep )
				var nW = hElement.scrollWidth - nScrollWidth
                if( hElement.scrollTop === 0 )
				{
					hElement.scrollTop = nScrollTop
				}
                if( hElement.scrollLeft === 0 )
				{
					hElement.scrollLeft =  nScrollLeft + nW
				}
			}
		}
		if( hEvent.preventDefault )
		{
			hEvent.preventDefault()
		}
	}
	hEvent.returnValue=true
	return true
}

cTranslator.install = function( hElement )
{
	if( document.attachEvent ) 
	{
		hElement.attachEvent( 'onfocus', cTranslator.initLanguage )
		hElement.attachEvent( 'onkeypress', cTranslator.processKey )
	}
	else if( document.addEventListener )
	{
		hElement.addEventListener( 'focus', cTranslator.initLanguage, false )
		hElement.addEventListener( 'keypress', cTranslator.processKey, false )
	}
}

cTranslator.init = function()
{
	var nI = 0
	var aInputs = document.getElementsByTagName( 'INPUT' )
    for( var I = 0; I < aInputs.length; I ++ )
	{
        if( aInputs[ I ].type.toLowerCase() === 'text' )
		{
            var sLangAtt = aInputs[ I ].getAttribute( 'MOLANG' )
			if( sLangAtt )
			{
                cTranslator.install( aInputs[ I ] )
			}
		}
	}
	var aTextAreas = document.getElementsByTagName( 'TEXTAREA' )
    for( var J = 0; J < aTextAreas.length; J ++ )
	{
        var sLang_Att = aTextAreas[ J ].getAttribute( 'MOLANG' )
        if( sLang_Att )
		{
            cTranslator.install( aTextAreas[ J ] )
		}
	}
	
	if( typeof CookieManager != 'undefined' )
	{
		var sLang = CookieManager.getCookie( 'molang' )
	}
    if( sLang !== null )
	{
		this.sGlobalLangID = sLang
		//this.switchLang( sLang )
	}
	this.switchLang( this.sGlobalLangID )
}


//replace incoming characters

//the functions used to translate
cTranslator.setSelectionRange = function( input, selectionStart, selectionEnd ) 
{
	if ( input.setSelectionRange )
	{
		input.focus()
		input.setSelectionRange(selectionStart, selectionEnd)
	}
	else if ( input.createTextRange )
	{
		var range = input.createTextRange()
		range.collapse(true)
		range.moveEnd('character', selectionEnd)
		range.moveStart('character', selectionStart)
		range.select()
	}
}

//mozilla only
cTranslator.replaceSelection = function( input, replaceString ) 
{
	if ( input.setSelectionRange )
	{
		var selectionStart = input.selectionStart
		var selectionEnd = input.selectionEnd
		input.value = 	input.value.substring(0, selectionStart)
						+ replaceString
						+ input.value.substring(selectionEnd)
		cTranslator.setSelectionRange(input, selectionStart + replaceString.length+1, selectionStart + replaceString.length+1)
	} 
}

//attach to onload event
cTranslator.onKeySwitch = function( hEvent )
{
   if( cTranslator.bUseKeySwitch )
   {
       if( hEvent === null ) hEvent = window.event
       var nCode = hEvent.keyCode ? hEvent.keyCode : hEvent.charCode ? hEvent.charCode : hEvent.which ? hEvent.which : 0;
	   if( hEvent.shiftKey && hEvent.ctrlKey )
	   {
		   cTranslator.hKeySwitchTimeout = setTimeout( function() { cTranslator.doKeySwitch() }, 200 )
	   }
	   else
	   {
	   		clearTimeout( cTranslator.hKeySwitchTimeout )
	   }
   }
}

cTranslator.onKeyUp = function( hEvent )
{
    if( hEvent === null ) hEvent = window.event
	var nCode = hEvent.keyCode ? hEvent.keyCode : hEvent.charCode ? hEvent.charCode : hEvent.which ? hEvent.which : void 0;
    if( nCode === 0 )
	{
		clearTimeout( cTranslator.hKeySwitchTimeout )
	}
}

cTranslator.doKeySwitch = function()
{
      cTranslator.toggleLang( cTranslator.hCurrentInput )
}


cTranslator.onLoad = function()
{
	cTranslator.onSwitchLang = cTranslator.displayLanguage
	
	if( bw )
	{
		if( bw.ie || ( bw.ns5 && bw.mozVersion > 1.3 ) )
		{
		  	cTranslator.init()

			var hLink = document.getElementById( 'langLink' )
            if( hLink !== null )
			{
				hLink.onclick = function() { cTranslator.toggleLang( cTranslator.hCurrentInput ); return false }
				
				var hHelpLink = document.getElementById( 'langHelpLink' )
                if( hHelpLink !== null )
				{
					hHelpLink.href = "http://momche.net/redir.php?page=inputlocalehelp"
				}
			}
		
			if( document.attachEvent )
			{
			   document.attachEvent( 'onkeydown', cTranslator.onKeySwitch )
			   document.attachEvent( 'onkeyup', cTranslator.onKeyUp )
			}
			else if( document.addEventListener )
			{
			   document.addEventListener( 'keydown', cTranslator.onKeySwitch, false )
			   document.addEventListener( 'keyup', cTranslator.onKeyUp, false )
			}

		}
	}
}

cTranslator.displayLanguage = function( sLang )
{
	var hLink = document.getElementById( 'langLink' )
    if( hLink !== null )
	{
		hLink.innerHTML = sLang
	}
}

if( window.attachEvent )
{
	window.attachEvent( 'onload', cTranslator.onLoad );
}
else if( window.addEventListener )
{
	window.addEventListener( 'load', cTranslator.onLoad, false );
}

