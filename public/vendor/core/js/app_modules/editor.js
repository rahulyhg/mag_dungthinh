!function(t){var e={};function i(n){if(e[n])return e[n].exports;var o=e[n]={i:n,l:!1,exports:{}};return t[n].call(o.exports,o,o.exports,i),o.l=!0,o.exports}i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{configurable:!1,enumerable:!0,get:n})},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=3)}([,,,function(t,e,i){i(4),i(5),i(6),i(7),i(8),i(9),i(10),i(11),i(12),i(13),i(14),i(15),i(16),i(17),i(18),i(19),i(20),i(21),i(22),i(23),t.exports=i(24)},function(t,e){var i={initCkeditor:function(t,e){var i={filebrowserImageBrowseUrl:RV_MEDIA_URL.base+"?media-action=select-files&method=ckeditor&type=image",filebrowserImageUploadUrl:RV_MEDIA_URL.media_upload_from_editor+"?method=ckeditor&type=image&_token="+$('meta[name="csrf-token"]').attr("content"),filebrowserWindowWidth:"768",filebrowserWindowHeight:"500",height:356,allowedContent:!0},n={};$.extend(n,i,e),CKEDITOR.replace(t,n)},initTinyMce:function(t){tinymce.init({menubar:!0,selector:"#"+t,skin:"voyager",min_height:300,resize:"vertical",plugins:"preview autolink advlist visualchars fullscreen link media template table charmap hr pagebreak nonbreaking anchor insertdatetime lists textcolor wordcount imagetools  contextmenu visualblocks",extended_valid_elements:"input[id|name|value|type|class|style|required|placeholder|autocomplete|onclick]",file_browser_callback:function(t,e,i,n){"image"===i&&$("#upload_file").trigger("click")},toolbar:"formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat code visualblocks",convert_urls:!1,image_caption:!0,image_advtab:!0,image_title:!0,entity_encoding:"raw",content_style:".mce-content-body {padding: 10px}"})},initEditor:function(t,e,n){if(t.length)switch(n){case"ckeditor":$.each(t,function(t,n){i.initCkeditor($(n).prop("id"),e)});break;case"tinymce":$.each(t,function(t,e){i.initTinyMce($(e).prop("id"))})}}};$(document).ready(function(){$(".editor-ckeditor").length>0&&i.initEditor($(".editor-ckeditor"),{},"ckeditor"),$(".editor-tinymce").length>0&&i.initEditor($(".editor-tinymce"),{},"tinymce"),$(document).on("click",".show-hide-editor-btn",function(t){t.preventDefault(),$("#"+$(this).data("result")).hasClass("editor-ckeditor")?void 0!==CKEDITOR.instances[$(this).data("result")]?(CKEDITOR.instances[$(this).data("result")].updateElement(),CKEDITOR.instances[$(this).data("result")].destroy(),$(".editor-action-item").not(".action-show-hide-editor").hide()):(i.initCkeditor($(this).data("result"),{},"ckeditor"),$(".editor-action-item").not(".action-show-hide-editor").show()):$("#"+$(this).data("result")).hasClass("editor-tinymce")&&tinymce.execCommand("mceToggleEditor",!1,$(this).data("result"))})})},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){},function(t,e){}]);