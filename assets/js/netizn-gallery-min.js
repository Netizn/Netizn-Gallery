jQuery(function($){var e,t=$("#netizn-gallery-field"),a=$("#netizn-gallery-container").find("ul.netizn-gallery-images");$(".add-images").on("click","a",function(i){var l=$(this);if(i.preventDefault(),e)return void e.open();e=wp.media.frames.netizn_gallery=wp.media({title:l.data("choose"),button:{text:l.data("update")},states:[new wp.media.controller.Library({title:l.data("choose"),filterable:"all",multiple:!0})]}),e.on("select",function(){var i=e.state().get("selection"),n=t.val();i.map(function(e){if(e=e.toJSON(),e.id){n=n?n+","+e.id:e.id;var t=e.sizes&&e.sizes.thumbnail?e.sizes.thumbnail.url:e.url;a.append('<li class="image" data-attachment_id="'+e.id+'"><img src="'+t+'" /><ul class="actions"><li><a href="#" class="delete" title="'+l.data("delete")+'">'+l.data("text")+"</a></li></ul></li>")}}),t.val(n)}),e.open()}),a.sortable({items:"li.image",cursor:"move",scrollSensitivity:40,forcePlaceholderSize:!0,forceHelperSize:!1,helper:"clone",opacity:.65,placeholder:"netizn-gallery-sortable-placeholder",start:function(e,t){t.item.css("background-color","#f6f6f6")},stop:function(e,t){t.item.removeAttr("style")},update:function(){var e="";$("#netizn-gallery-container").find("ul li.image").css("cursor","default").each(function(){e=e+$(this).attr("data-attachment_id")+","}),t.val(e)}}),$("#netizn-gallery-container").on("click","a.delete",function(){$(this).closest("li.image").remove();var e="";return $("#netizn-gallery-container").find("ul li.image").css("cursor","default").each(function(){e=e+$(this).attr("data-attachment_id")+","}),t.val(e),$("#tiptip_holder").removeAttr("style"),$("#tiptip_arrow").removeAttr("style"),!1})});