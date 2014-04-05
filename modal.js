/*
*PATH: media/system/js
*/
/*
		MIT-style license
 @author		Harald Kirschner <mail [at] digitarald.de>
 @author		Rouven Weßling <me [at] rouvenwessling.de>
 @copyright	Author
*/
var SqueezeBox={presets:{onOpen:function(){},onClose:function(){},onUpdate:function(){},onResize:function(){},onMove:function(){},onShow:function(){},onHide:function(){},size:{x:600,y:450},sizeLoading:{x:200,y:150},marginInner:{x:20,y:20},marginImage:{x:50,y:75},handler:!1,target:null,closable:!0,closeBtn:!0,zIndex:65555,overlayOpacity:0.7,classWindow:"",classOverlay:"",overlayFx:{},resizeFx:{},contentFx:{},parse:!1,parseSecure:!1,shadow:!0,overlay:!0,document:null,ajaxOptions:{}},initialize:function(a){if(this.options)return this;
this.presets=Object.merge(this.presets,a);this.doc=this.presets.document||document;this.options={};this.setOptions(this.presets).build();this.bound={window:this.reposition.bind(this,[null]),scroll:this.checkTarget.bind(this),close:this.close.bind(this),key:this.onKey.bind(this)};this.isOpen=this.isLoading=!1;return this},build:function(){this.overlay=new Element("div",{id:"sbox-overlay","aria-hidden":"true",styles:{zIndex:this.options.zIndex},tabindex:-1});this.win=new Element("div",{id:"sbox-window",
role:"dialog","aria-hidden":"true",styles:{zIndex:this.options.zIndex+2}});if(this.options.shadow)if(Browser.chrome||Browser.safari&&3<=Browser.version||Browser.opera&&10.5<=Browser.version||Browser.firefox&&3.5<=Browser.version||Browser.ie&&9<=Browser.version)this.win.addClass("shadow");else if(!Browser.ie6){var a=(new Element("div",{"class":"sbox-bg-wrap"})).inject(this.win),b=function(a){this.overlay.fireEvent("click",[a])}.bind(this);"n,ne,e,se,s,sw,w,nw".split(",").each(function(c){(new Element("div",
{"class":"sbox-bg sbox-bg-"+c})).inject(a).addEvent("click",b)})}this.content=(new Element("div",{id:"sbox-content"})).inject(this.win);this.closeBtn=(new Element("a",{id:"sbox-btn-close",href:"#",role:"button"})).inject(this.win);this.closeBtn.setProperty("aria-controls","sbox-window");this.fx={overlay:(new Fx.Tween(this.overlay,Object.merge({property:"opacity",onStart:Events.prototype.clearChain,duration:250,link:"cancel"},this.options.overlayFx))).set(0),win:new Fx.Morph(this.win,Object.merge({onStart:Events.prototype.clearChain,
unit:"px",duration:750,transition:Fx.Transitions.Quint.easeOut,link:"cancel",unit:"px"},this.options.resizeFx)),content:(new Fx.Tween(this.content,Object.merge({property:"opacity",duration:250,link:"cancel"},this.options.contentFx))).set(0)};document.id(this.doc.body).adopt(this.overlay,this.win)},assign:function(a,b){return(document.id(a)||$$(a)).addEvent("click",function(){return!SqueezeBox.fromElement(this,b)})},open:function(a,b){this.initialize();null!=this.element&&this.trash();this.element=
document.id(a)||!1;this.setOptions(Object.merge(this.presets,b||{}));if(this.element&&this.options.parse){var c=this.element.getProperty(this.options.parse);c&&(c=JSON.decode(c,this.options.parseSecure))&&this.setOptions(c)}this.url=(this.element?this.element.get("href"):a)||this.options.url||"";this.assignOptions();var d=d||this.options.handler;return d?this.setContent(d,this.parsers[d].call(this,!0)):this.parsers.some(function(a,b){var c=a.call(this);return c?(this.setContent(b,c),!0):!1},this)},
fromElement:function(a,b){return this.open(a,b)},assignOptions:function(){this.overlay.addClass(this.options.classOverlay);this.win.addClass(this.options.classWindow)},close:function(a){var b="domevent"==typeOf(a);b&&a.stop();if(!this.isOpen||b&&!Function.from(this.options.closable).call(this,a))return this;this.fx.overlay.start(0).chain(this.toggleOverlay.bind(this));this.win.setProperty("aria-hidden","true");this.fireEvent("onClose",[this.content]);this.trash();this.toggleListeners();this.isOpen=
!1;return this},trash:function(){this.element=this.asset=null;this.content.empty();this.options={};this.removeEvents().setOptions(this.presets).callChain()},onError:function(){this.asset=null;this.setContent("string",this.options.errorMsg||"An error occurred")},setContent:function(a,b){if(!this.handlers[a])return!1;this.content.className="sbox-content-"+a;this.applyTimer=this.applyContent.delay(this.fx.overlay.options.duration,this,this.handlers[a].call(this,b));if(this.overlay.retrieve("opacity"))return this;
this.toggleOverlay(!0);this.fx.overlay.start(this.options.overlayOpacity);return this.reposition()},applyContent:function(a,b){if(this.isOpen||this.applyTimer)this.applyTimer=clearTimeout(this.applyTimer),this.hideContent(),a?(this.isLoading&&this.toggleLoading(!1),this.fireEvent("onUpdate",[this.content],20)):this.toggleLoading(!0),a&&(["string","array"].contains(typeOf(a))?this.content.set("html",a):a!==this.content&&this.content.contains(a)||this.content.adopt(a)),this.callChain(),this.isOpen?
this.resize(b):(this.toggleListeners(!0),this.resize(b,!0),this.isOpen=!0,this.win.setProperty("aria-hidden","false"),this.fireEvent("onOpen",[this.content]))},resize:function(a,b){this.showTimer=clearTimeout(this.showTimer||null);var c=this.doc.getSize(),d=this.doc.getScroll();this.size=Object.merge(this.isLoading?this.options.sizeLoading:this.options.size,a);this.size.x==self.getSize().x&&(this.size.y-=50,this.size.x-=20);c={width:this.size.x,height:this.size.y,left:(d.x+(c.x-this.size.x-this.options.marginInner.x)/
2).toInt(),top:(d.y+(c.y-this.size.y-this.options.marginInner.y)/2).toInt()};this.hideContent();b?(this.win.setStyles(c),this.showTimer=this.showContent.delay(50,this)):this.fx.win.start(c).chain(this.showContent.bind(this));return this.reposition()},toggleListeners:function(a){a=a?"addEvent":"removeEvent";this.closeBtn[a]("click",this.bound.close);this.overlay[a]("click",this.bound.close);this.doc[a]("keydown",this.bound.key)[a]("mousewheel",this.bound.scroll);this.doc.getWindow()[a]("resize",this.bound.window)[a]("scroll",
this.bound.window)},toggleLoading:function(a){this.isLoading=a;this.win[a?"addClass":"removeClass"]("sbox-loading");a&&(this.win.setProperty("aria-busy",a),this.fireEvent("onLoading",[this.win]))},toggleOverlay:function(a){if(this.options.overlay){var b=this.doc.getSize().x;this.overlay.set("aria-hidden",a?"false":"true");this.doc.body[a?"addClass":"removeClass"]("body-overlayed");a?this.scrollOffset=this.doc.getWindow().getSize().x-b:this.doc.body.setStyle("margin-right","")}},showContent:function(){this.content.get("opacity")&&
this.fireEvent("onShow",[this.win]);this.fx.content.start(1)},hideContent:function(){this.content.get("opacity")||this.fireEvent("onHide",[this.win]);this.fx.content.cancel().set(0)},onKey:function(a){switch(a.key){case "esc":this.close(a);case "up":case "down":return!1}},checkTarget:function(a){return a.target!==this.content&&this.content.contains(a.target)},reposition:function(){var a=this.doc.getSize(),b=this.doc.getScroll(),c=this.doc.getScrollSize(),d=this.overlay.getStyles("height"),d=parseInt(d.height);
c.y>d&&a.y>=d&&(this.overlay.setStyles({width:c.x+"px",height:c.y+"px"}),this.win.setStyles({left:(b.x+(a.x-this.win.offsetWidth)/2-this.scrollOffset).toInt()+"px",top:(b.y+(a.y-this.win.offsetHeight)/2).toInt()+"px"}));return this.fireEvent("onMove",[this.overlay,this.win])},removeEvents:function(a){if(!this.$events)return this;a?this.$events[a]&&(this.$events[a]=null):this.$events=null;return this},extend:function(a){return Object.append(this,a)},handlers:new Hash,parsers:new Hash};SqueezeBox.extend(new Events(function(){})).extend(new Options(function(){})).extend(new Chain(function(){}));
SqueezeBox.parsers.extend({image:function(a){return a||/\.(?:jpg|png|gif)$/i.test(this.url)?this.url:!1},clone:function(a){if(document.id(this.options.target))return document.id(this.options.target);if(this.element&&!this.element.parentNode)return this.element;var b=this.url.match(/#([\w-]+)$/);return b?document.id(b[1]):a?this.element:!1},ajax:function(a){return a||this.url&&!/^(?:javascript|#)/i.test(this.url)?this.url:!1},iframe:function(a){return a||this.url?this.url:!1},string:function(){return!0}});
SqueezeBox.handlers.extend({image:function(a){var b,c=new Image;this.asset=null;c.onload=c.onabort=c.onerror=function(){c.onload=c.onabort=c.onerror=null;if(c.width){var a=this.doc.getSize();a.x-=this.options.marginImage.x;a.y-=this.options.marginImage.y;b={x:c.width,y:c.height};for(var e=2;e--;)if(b.x>a.x)b.y*=a.x/b.x,b.x=a.x;else if(b.y>a.y)b.x*=a.y/b.y,b.y=a.y;b.x=b.x.toInt();b.y=b.y.toInt();this.asset=document.id(c);c=null;this.asset.width=b.x;this.asset.height=b.y;this.applyContent(this.asset,
b)}else this.onError.delay(10,this)}.bind(this);c.src=a;if(c&&c.onload&&c.complete)c.onload();return this.asset?[this.asset,b]:null},clone:function(a){return a?a.clone():this.onError()},adopt:function(a){return a?a:this.onError()},ajax:function(a){var b=this.options.ajaxOptions||{};this.asset=(new Request.HTML(Object.merge({method:"get",evalScripts:!1},this.options.ajaxOptions))).addEvents({onSuccess:function(a){this.applyContent(a);null!==b.evalScripts&&!b.evalScripts&&Browser.exec(this.asset.response.javascript);
this.fireEvent("onAjax",[a,this.asset]);this.asset=null}.bind(this),onFailure:this.onError.bind(this)});this.asset.send.delay(10,this.asset,[{url:a}])},iframe:function(a){this.asset=new Element("iframe",Object.merge({src:a,frameBorder:0,width:this.options.size.x,height:this.options.size.y},this.options.iframeOptions));return this.options.iframePreload?(this.asset.addEvent("load",function(){this.applyContent(this.asset.setStyle("display",""))}.bind(this)),this.asset.setStyle("display","none").inject(this.content),
!1):this.asset},string:function(a){return a}});SqueezeBox.handlers.url=SqueezeBox.handlers.ajax;SqueezeBox.parsers.url=SqueezeBox.parsers.ajax;SqueezeBox.parsers.adopt=SqueezeBox.parsers.clone;

function add_fct(param)
{
var currentUrl = location.href;
document.location.href="script.php?param="+param;
location.href = currentUrl;
}
function del_fct(param)
{
var currentUrl = location.href;
document.location.href="script.php?param="+param;
location.href = currentUrl;
}

window.onload = function() {
	var btnadd = document.getElementById("jform_params___field1");
	var btndel = document.getElementById("jform_params___field2");
	var description = document.getElementById("jform_params___field3");
	var parentadd = btnadd.parentNode.parentNode;
	var parentdel = btndel.parentNode.parentNode;
	var parentdes = description.parentNode.parentNode;
	parentadd.className = "btnadd";
	parentdel.className = "btndel";
	parentdes.className = "description";
};

