!function(e){var t={};function o(n){if(t[n])return t[n].exports;var r=t[n]={i:n,l:!1,exports:{}};return e[n].call(r.exports,r,r.exports,o),r.l=!0,r.exports}o.m=e,o.c=t,o.d=function(e,t,n){o.o(e,t)||Object.defineProperty(e,t,{configurable:!1,enumerable:!0,get:n})},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="/",o(o.s=102)}({102:function(e,t,o){e.exports=o(103)},103:function(e,t){var o=function(){function e(e,t){for(var o=0;o<t.length;o++){var n=t[o];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,o,n){return o&&e(t.prototype,o),n&&e(t,n),t}}();var n=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}return o(e,[{key:"init",value:function(){$(document).on("click",".btn-trigger-active-theme",function(e){e.preventDefault();var t=$(e.currentTarget);t.addClass("button-loading"),$.ajax({url:route("theme.active"),data:{theme:t.data("theme")},type:"POST",success:function(e){e.error?Botble.showNotice("error",e.message):(Botble.showNotice("success",e.message),window.location.reload()),t.removeClass("button-loading")},error:function(e){Botble.handleError(e),t.removeClass("button-loading")}})}),$(document).on("click",".btn-trigger-remove-theme",function(e){e.preventDefault(),$("#confirm-remove-theme-button").data("theme",$(e.currentTarget).data("theme")),$("#remove-theme-modal").modal("show")}),$(document).on("click","#confirm-remove-theme-button",function(e){e.preventDefault();var t=$(e.currentTarget);t.addClass("button-loading"),$.ajax({url:route("theme.remove",{theme:t.data("theme")}),type:"POST",success:function(e){e.error?Botble.showNotice("error",e.message):(Botble.showNotice("success",e.message),window.location.reload()),t.removeClass("button-loading"),$("#remove-theme-modal").modal("hide")},error:function(e){Botble.handleError(e),t.removeClass("button-loading"),$("#remove-theme-modal").modal("hide")}})})}}]),e}();$(document).ready(function(){(new n).init()})}});