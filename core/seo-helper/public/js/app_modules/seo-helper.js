!function(e){function t(o){if(n[o])return n[o].exports;var i=n[o]={i:o,l:!1,exports:{}};return e[o].call(i.exports,i,i.exports,t),i.l=!0,i.exports}var n={};t.m=e,t.c=n,t.d=function(e,n,o){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:o})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=48)}({48:function(e,t,n){e.exports=n(49)},49:function(e,t){var n=n||{};n.handleMetaBox=function(){$(".page-url-seo p").text($(document).find("#sample-permalink a").prop("href")),$(document).on("click",".btn-trigger-show-seo-detail",function(e){e.preventDefault(),$(".seo-edit-section").toggleClass("hidden")}),$(document).on("keyup","input[name=name]",function(){n.updateSEOTitle($(this).val())}),$(document).on("keyup","input[name=title]",function(){n.updateSEOTitle($(this).val())}),$(document).on("keyup","textarea[name=description]",function(){n.updateSEODescription($(this).val())}),$(document).on("keyup","#seo_title",function(){$(this).val()?($(".page-title-seo").text($(this).val()),$(".default-seo-description").addClass("hidden"),$(".existed-seo-meta").removeClass("hidden")):$("input[name=name]").val()?$(".page-title-seo").text($("input[name=name]").val()):$(".page-title-seo").text($("input[name=title]").val())}),$(document).on("keyup","#seo_description",function(){$(this).val()?$(".page-description-seo").text($(this).val()):$(".page-title-seo").text($("textarea[name=description]").val())})},n.updateSEOTitle=function(e){e?($("#seo_title").val()||$(".page-title-seo").text(e),$(".default-seo-description").addClass("hidden"),$(".existed-seo-meta").removeClass("hidden")):($(".default-seo-description").removeClass("hidden"),$(".existed-seo-meta").addClass("hidden"))},n.updateSEODescription=function(e){e&&($("#seo_description").val()||$(".page-description-seo").text(e))},$(document).ready(function(){n.handleMetaBox()})}});