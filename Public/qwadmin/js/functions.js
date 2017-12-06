var step=0
function flash_title()
{
    step++;
    if (step==3) {
        step=1
    }
    if (step==1) {
        document.title='【你有新的消息等待处理】'
    }
    if (step==2) {
        document.title='【　　　　　　        '
    }

    setTimeout("flash_title()",500);
}



function get_notice_flash_title(){
    $.post(
        "/Dashboard/get_notice",
        function(msg){
            if(msg.status == 0){
                flash_title();
            }
            if(msg.status == 1){
                setTimeout("get_notice_flash_title()",30000);
            }
        },
        'json'
    );
}
(function() {
	$(".sidebar-inner").slimScroll({
		height: "100%",
		size: "3px",
		color: "#666"
	});

    $(".c-sb").slimScroll({
		height: "95%",
		size: "3px",
		color: "#666"
	});
	$(".main-menu>li").each(function() {
		var h = $(this).find(".counts").size();
		if (h > 0) {
			$(this).find("i.pull-right").remove();
			$(this).children("a").append('<b class="counts-1" title="有未处理的信息" alt="有未处理的信息"></b>')
		}
	});
	$(".main-menu-sm>li").each(function() {
		var h = $(this).find(".counts").size();
		if (h > 0) {
			$(this).find("i.pull-right").remove();
			$(this).children("a").append('<b class="counts-1" title="有未处理的信息" alt="有未处理的信息"></b>')
		}
	});

})();


(function() {
	var layoutStatus = localStorage.getItem("ma-layout-status");
	if (layoutStatus == 1) {
		$("body").addClass("sw-toggled");
		$("#tw-switch").prop("checked", true)
	}
	$("body").on("change", "#toggle-width input:checkbox", function() {
		if ($(this).is(":checked")) {
			setTimeout(function() {
				$("body").addClass("toggled sw-toggled");
				localStorage.setItem("ma-layout-status", 1)
			}, 250)
		} else {
			setTimeout(function() {
				$("body").removeClass("toggled sw-toggled");
				localStorage.setItem("ma-layout-status", 0);
				$(".main-menu > li").removeClass("animated")
			}, 250)
		}
	})
})();


if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
	$("html").addClass("ismobile")
}

$(document).ready(function() {



	(function() {

		$("body").on("click", ".sub-menu > a", function(e) {
			e.preventDefault();
			$(this).next().slideToggle(200);
			$(this).parent().toggleClass("toggled")
		});

        $('body').on('mouseenter','#sidebar',function(){
            $("#sidebar").addClass('open');
        });
        $('body').on('mouseleave','#sidebar',function(){
            $("#sidebar").removeClass('open');
        });

	})();
	$("body").on("click", '[data-clear="notification"]', function(e) {
		e.preventDefault();
		var x = $(this).closest(".listview");
		var y = x.find(".lv-item");
		var z = y.size();
		$(this).parent().fadeOut();
		x.find(".list-group").prepend('<i class="grid-loading hide-it"></i>');
		x.find(".grid-loading").fadeIn(1500);
		var w = 0;
		y.each(function() {
			var z = $(this);
			setTimeout(function() {
				z.addClass("animated fadeOutRightBig").delay(1000).queue(function() {
					z.remove()
				})
			}, w += 150)
		});
		setTimeout(function() {
			$("#notifications").addClass("empty")
		}, (z * 150) + 200)
	});
	if ($(".dropdown")[0]) {
		$("body").on("click", ".dropdown.open .dropdown-menu", function(e) {
			e.stopPropagation()
		});
		$(".dropdown").on("shown.bs.dropdown", function(e) {
			if ($(this).attr("data-animation")) {
				$animArray = [];
				$animation = $(this).data("animation");
				$animArray = $animation.split(",");
				$animationIn = "animated " + $animArray[0];
				$animationOut = "animated " + $animArray[1];
				$animationDuration = "";
				if (!$animArray[2]) {
					$animationDuration = 500
				} else {
					$animationDuration = $animArray[2]
				}
				$(this).find(".dropdown-menu").removeClass($animationOut);
				$(this).find(".dropdown-menu").addClass($animationIn)
			}
		});
		$(".dropdown").on("hide.bs.dropdown", function(e) {
			if ($(this).attr("data-animation")) {
				e.preventDefault();
				$this = $(this);
				$dropdownMenu = $this.find(".dropdown-menu");
				$dropdownMenu.addClass($animationOut);
				setTimeout(function() {
					$this.removeClass("open")
				}, $animationDuration)
			}
		})
	}
	$("body").on("click", ".profile-menu > a", function(e) {
		e.preventDefault();
		$(this).parent().toggleClass("toggled");
		$(this).next().slideToggle(200)
	});

	if ($(".tag-select")[0]) {
		$(".tag-select").chosen({
			width: "100%",
			allow_single_deselect: true
		})
	}
	if ($(".input-slider")[0]) {
		$(".input-slider").each(function() {
			var isStart = $(this).data("is-start");
			$(this).noUiSlider({
				start: isStart,
				range: {
					"min": 0,
					"max": 100,
				}
			})
		})
	}
	if ($("input-mask")[0]) {
		$(".input-mask").mask()
	}
	if ($(".color-picker")[0]) {
		$(".color-picker").each(function() {
			$(".color-picker").each(function() {
				var colorOutput = $(this).closest(".cp-container").find(".cp-value");
				$(this).farbtastic(colorOutput)
			})
		})
	}
	if ($(".date-time-picker")[0]) {
		$(".date-time-picker").datetimepicker()
	}
	if ($(".time-picker")[0]) {
		$(".time-picker").datetimepicker({
			format: "LT"
		})
	}
	if ($(".date-picker")[0]) {
		$(".date-picker").datetimepicker({
			format: "DD/MM/YYYY"
		})
	}
	function notify(message, type) {
		$.growl({
			message: message
		}, {
			type: type,
			allow_dismiss: false,
			label: "Cancel",
			className: "btn-xs btn-inverse",
			placement: {
				from: "top",
				align: "right"
			},
			delay: 2500,
			animate: {
				enter: "animated bounceIn",
				exit: "animated bounceOut"
			},
			offset: {
				x: 20,
				y: 85
			}
		})
	}
//    (function() {
//		Waves.attach(".btn:not(.btn-icon):not(.btn-float)");
//		Waves.attach(".btn-icon, .btn-float", ["waves-circle", "waves-float"]);
//		Waves.init()
//	})();
	if ($(".lightbox")[0]) {
		$(".lightbox").lightGallery({
			enableTouch: true
		})
	}
	if ($(".collapse")[0]) {
		$(".collapse").on("show.bs.collapse", function(e) {
			$(this).closest(".panel").find(".panel-heading").addClass("active")
		});
		$(".collapse").on("hide.bs.collapse", function(e) {
			$(this).closest(".panel").find(".panel-heading").removeClass("active")
		});
		$(".collapse.in").each(function() {
			$(this).closest(".panel").find(".panel-heading").addClass("active")
		})
	}
	if ($('[data-toggle="tooltip"]')[0]) {
		$('[data-toggle="tooltip"]').tooltip()
	}
	if ($('[data-toggle="popover"]')[0]) {
		$('[data-toggle="popover"]').popover()
	}
	if ($(".on-select")[0]) {
		var checkboxes = ".lv-avatar-content input:checkbox";
		var actions = $(".on-select").closest(".lv-actions");
		$("body").on("click", checkboxes, function() {
			if ($(checkboxes + ":checked")[0]) {
				actions.addClass("toggled")
			} else {
				actions.removeClass("toggled")
			}
		})
	}
	if ($(".login-content")[0]) {
		$("html").addClass("login-content");
		$("body").on("click", ".login-navigation > li", function() {
			var z = $(this).data("block");
			var t = $(this).closest(".lc-block");
			t.removeClass("toggled");
			setTimeout(function() {
				$(z).addClass("toggled")
			})
		})
	}
	if ($('[data-action="fullscreen"]')[0]) {
		var fs = $("[data-action='fullscreen']");
		fs.on("click", function(e) {
			e.preventDefault();

			function launchIntoFullscreen(element) {
				if (element.requestFullscreen) {
					element.requestFullscreen()
				} else {
					if (element.mozRequestFullScreen) {
						element.mozRequestFullScreen()
					} else {
						if (element.webkitRequestFullscreen) {
							element.webkitRequestFullscreen()
						} else {
							if (element.msRequestFullscreen) {
								element.msRequestFullscreen()
							}
						}
					}
				}
			}
			function exitFullscreen() {
				if (document.exitFullscreen) {
					document.exitFullscreen()
				} else {
					if (document.mozCancelFullScreen) {
						document.mozCancelFullScreen()
					} else {
						if (document.webkitExitFullscreen) {
							document.webkitExitFullscreen()
						}
					}
				}
			}
			launchIntoFullscreen(document.documentElement);
			fs.closest(".dropdown").removeClass("open")
		})
	}

	if ($("[data-pmb-action]")[0]) {
		$("body").on("click", "[data-pmb-action]", function(e) {
			e.preventDefault();
			var d = $(this).data("pmb-action");
			if (d === "edit") {
				$(this).closest(".pmb-block").toggleClass("toggled")
			}
			if (d === "reset") {
				$(this).closest(".pmb-block").removeClass("toggled")
			}
		})
	}
	if ($("html").hasClass("ie9")) {
		$("input, textarea").placeholder({
			customClass: "ie9-placeholder"
		})
	}
	if ($(".lvh-search-trigger")[0]) {
		$("body").on("click", ".lvh-search-trigger", function(e) {
			e.preventDefault();
			x = $(this).closest(".lv-header-alt").find(".lvh-search");
			x.fadeIn(300);
			x.find(".lvhs-input").focus()
		});
		$("body").on("click", ".lvh-search-close", function() {
			x.fadeOut(300);
			setTimeout(function() {
				x.find(".lvhs-input").val("")
			}, 350)
		})
	}
	if ($('[data-action="print"]')[0]) {
		$("body").on("click", '[data-action="print"]', function(e) {
			e.preventDefault();
			window.print()
		})
	}
	$("#sidebar .main-menu ul").each(function() {
		var quanx_str = $(this).html().replace(/\s+/g, "");
		if (quanx_str === "") {
			$(this).parent().hide()
		}
	});



});


 if($('.input-limit')[0]){
     $(document).on('keyup','.input-limit',function(){
         var _limit = parseInt($(this).attr('data-limit'));
         var _size = $(this).val().length;
         
         if(_size >= _limit){
             $(this).val($(this).val().substr(0,_limit));
         }
     })
 }





$(".main-menu-sm>li").each(function() {
	if ($(this).find("li").size() == 0) {
		$(this).hide()
	}
});



function Sidebar(){
    $('body').on('click','.toggle-popup',function(e){

        e.preventDefault();
        var xy = $(this).data('popup');
        var $elem1 = '';
        var $elem2 = '';

        $(this).toggleClass('open');

        $elem1 = xy;
        $elem2 = '.toggle-popup';

        $(xy).toggleClass('toggled');
        $('#header').toggleClass('r-sidebar-toggled');

        if($(xy).find('.no-searchbox').size()>=1){
            $('.r-content').css('height','100%')
        }

        if($('#header').hasClass('r-sidebar-toggled')){
            $(document).on('click', function (e) {
                if (($(e.target).closest($elem1).length === 0) && ($(e.target).closest($elem2).length === 0)) {
                    setTimeout(function(){
                        $($elem1).removeClass('toggled');
                        $($elem2).removeClass('open');
                        $('#header').removeClass('r-sidebar-toggled');
                    });
                }
            });
        }

    });

    $('body').on('click','.close_side',function(){
        $('.rightside').removeClass('toggled');
        $('.toggle-popup').removeClass('open');
        $('#header').removeClass('r-sidebar-toggled');
    });


    $('.rightside .r-content').slimScroll({
        height: "calc(100% - 90px)"
    });

    $(document).on('click','.fake-save',function(){
        sweetAlert({title:'保存成功',text: '',type: "success",timer:1000,showConfirmButton: false});
        setTimeout(function(){window.location.reload()},1000);

    });

     $(document).on('click','.fake-back',function(){
        $('.rightside').removeClass('toggled');
        $('.toggle-popup').removeClass('open');
        $('#header').removeClass('r-sidebar-toggled');

    });


}

Sidebar();

function nextnode(node){//寻找下一个兄弟并剔除空的文本节点

    if(!node){
        return
    };
    if(node.nodeType == 1){
        return node;
    }

    if(node.nextSibling){
        return nextnode(node.nextSibling);
    }

}
function prevnode(node){//寻找上一个兄弟并剔除空的文本节点


     if(!node){
         return
     };

     if(node.nodeType == 1){
        return node;
     }

     if(node.previousSibling){
        return prevnode(node.previousSibling);
     }

}

function parcheck(self,checked){//递归寻找父元素，并找到input元素进行操作

    var par =  prevnode(self.parentNode.parentNode.parentNode.previousSibling),parspar;

    if(par&&par.getElementsByTagName('input')[0]){

        par.getElementsByTagName('input')[0].checked = checked;
        parcheck(par.getElementsByTagName('input')[0],sibcheck(par.getElementsByTagName('input')[0]));
    }
}
function sibcheck(self){//判断兄弟节点是否已经全部选中

    var sbi = self.parentNode.parentNode.parentNode.childNodes,n=0;
    for(var i=0;i<sbi.length;i++){

        if(sbi[i].nodeType != 1){//由于子结点中包括空的文本节点，所以这里累计长度的时候也要算上去
             n++;
        }
        else if(sbi[i].getElementsByTagName('input')[0].checked){
             n++;
        }

  }

  return n==sbi.length?true:false;
}
$(document).on('click','.tree-list',function(e){

     e = e||window.event;
     var target = e.target||e.srcElement;
     var tp = nextnode(target.parentNode.nextSibling);
     switch(target.nodeName){
		 case 'A'://点击A标签展开和收缩树形目录，并改变其样式会选中checkbox
                if(tp&&tp.nodeName == 'UL'){

                    if(tp.style.display != 'block' ){
                        tp.style.display = 'block';
                        prevnode(target.parentNode.previousSibling).className = 'ren'
                    }else{
                        tp.style.display = 'none';
                        prevnode(target.parentNode.previousSibling).className = 'add'
                    }
                }
                break;
           case 'SPAN'://点击图标只展开或者收缩
                var ap = nextnode(nextnode(target.nextSibling).nextSibling);
                if(ap.style.display != 'block' ){
                    ap.style.display = 'block';
                    target.className = 'ren'
                }else{
                    ap.style.display = 'none';
                    target.className = 'add'
                }
                break;

           case 'INPUT'://点击checkbox，父元素选中，则子节点中的checkbox也同时选中，子结点取消父元素随之取消
                if(target.checked){
                    if(tp){
                        var checkbox = tp.getElementsByTagName('input');
                        for(var i=0;i<checkbox.length;i++)
                            checkbox[i].checked = true;
                        }
                    }else{
                        if(tp){
                            var checkbox = tp.getElementsByTagName('input');
                            for(var i=0;i<checkbox.length;i++)
                                checkbox[i].checked = false;
                            }
                    }
                parcheck(target,sibcheck(target));//当子结点取消选中的时候调用该方法递归其父节点的checkbox逐一取消选中
                break;
        }
 });

topSearchBar();

function topSearchBar(){
    var $bar = $('.search-advance');
    var $box = $('.list-search-area');
    var $close = $('.close-search');
    var $clear = $('.clear-search');

    $bar.click(function(){
        $box.toggleClass('hide');
        if($box.hasClass('hide')){
            $bar.html('高级搜索');
            $box.next('.list-action-grid').find('.search-keywords').attr('disabled',false);
            $box.find('input,select').attr('disabled',true);
        }else{
            $bar.html('普通搜索');
            $box.next('.list-action-grid').find('.search-keywords').attr('disabled',true);
            $box.find('input,select').attr('disabled',false);
        }
    });

    $close.click(function(){
        $box.addClass('hide');
        $bar.html('高级搜索');
        $clear.trigger("click");
        $box.next('.list-action-grid').find('.search-keywords').attr('disabled',false);
        resetForm($box);
    });

    $clear.click(function(e){
        e.preventDefault();
        resetForm($box);
    });

}

window.onload = function(){
    if($('.list-search-area').hasClass('hide')){
        $('.list-search-area').find('input').attr('disabled',true);
        $('.list-search-area').find('select').attr('disabled',true);
    }else{
        $('.list-search-area').find('input').attr('disabled',false);
        $('.list-search-area').find('select').attr('disabled',false);
    }
}

function resetForm(b){
     b.find('input').val('');
     b.find('select').each(function(){
         $(this).find('option:first').prop("selected",'selected');
     })
}

function textCount(){
    $('.textarea-area textarea').keyup(function(){
        var totalNum = parseInt($('.total-num').text());
        var nowNum = $(this).val().length;

        $('.text-count .now-num').text(nowNum);
        if(nowNum>=totalNum){
            $(this).val($(this).val().substr(0,totalNum));
            $('.text-count .now-num').text(totalNum);
        }
    })
}

/**
 *[imgbDzUpload 上传图片]
 *@param  {string} target [上传按钮]
 *@param  {string} url [服务端地址]
 *@param  {number} len [up_img的个数，默认都为0]
 *@param  {string} name [input的name]
 *@param  {number} max [最大上传数量]
 *@param  {string} dz [初始化Dropzone 类对象名称]
 *@param  {string} list [显示预览的列表]
 *@return {}  []
 */
function imgDzUpload(target,url,len,name,max,dz,list,Filesize,imgdomain){
    if(!Filesize){
        Filesize = 1;
    }
    if(!imgdomain){
        imgdomain = '';
    }
    dz = new Dropzone(target,{
            url: url,
            dictMaxFilesExceeded:'此文件不会生效',
            dictRemoveFile:'删除此文件',
            dictDefaultMessage:'请上传图片',
            dictInvalidInputType:'不是图片文件',
            dictFileTooBig:'文件太大',
            maxFilesize:Filesize, //图片大小MB
            realMaxSize:max, // 图片数量
            addRemoveLinks:true,
            dictRemoveFile:'1',
            previewTemplate:'<div class="file-item"><div class="state-mark"></div><span class="upload-picture-opts"><i class="fa fa-trash"></i></span></div>',
            previewsContainer:list,
            init: function() {
                this.on("success", function(file,msg) {
                    if(msg.status==1){//上传图片成功
                        if(len>=dz.options.realMaxSize){
                             $(file.previewTemplate).remove();
                             sweetAlert({title:'上传失败，最多可上传'+dz.options.realMaxSize+'张图片。',text: '',type: "warning",timer:2000});
                        }else{
                             $(file.previewTemplate).addClass('up-state-done').css('background','url(https://img.weixiaoqu.com'+msg.data+')');
                             $(file.previewTemplate).append('<input class="up_img" name="'+name+'"  type="hidden" value="'+imgdomain+msg.data+'" />');
                        }
                    }
                    if(msg.status==0){
                        sweetAlert({title:msg.info,text: '',type: "warning",timer:2000});
                    }
                });

                this.on('error',function(file){
                     $(file.previewTemplate).remove();
                     sweetAlert({title:'上传图片失败。',text: '',type: "warning",timer:2000});
                });

                this.on("removedfile", function(file) {
                    len = $('#upload1').parents('.upload-area').find('.up_img').size();
                });

            }
        });


        $('.dz-edit').on('click','.dz-remove',function(){
            $(this).parent('.dz-edit').remove();
            len = $(target).parents('.upload-area').find('.up_img').size();
        });

        $(target).click(function(){
            len = $(target).parents('.upload-area').find('.up_img').size();
        })

 }

/* window.onload = function(){//页面加载时给有子结点的元素动态添加图标
      var labels = $('.tree-list label');
      for(var i=0;i<labels.length;i++){
           var span = document.createElement('span');
           span.style.cssText ='display:inline-block;height:18px;vertical-align:middle;width:16px;cursor:pointer;';
           span.innerHTML = ' '
           span.className = 'add';
           if(nextnode(labels[i].nextSibling)&&nextnode(labels[i].nextSibling).nodeName == 'UL'){
                labels[i].parentNode.insertBefore(span,labels[i]);
           }else{
             labels[i].className = 'rem'
           }

      }
}*/


$('.house_btn').click(function(){
	var x_id = $(this).attr('data-xq-id');
	var xq_li = $('.xq_li').attr('data-id');
	$('#rightside_zhuhu .xq_li').hide();
	$('#rightside_zhuhu .li_'+x_id).show();
	$('.h_id').val($(this).attr('data-id'));
	$('.r-content').find('input').removeAttr('checked');
	$('.x_id').val(x_id);
});


$('.js_check_all').click(function(){
    if($(this).is(":checked")){
        $(this).parents('table').children('tbody').children('tr').find('td:first').find('.wxq-checkbox[type="checkbox"]').prop('checked',true);
    }else{
        $(this).parents('table').children('tbody').children('tr').find('td:first').find('.wxq-checkbox[type="checkbox"]').prop('checked',false);
    }
})



//绑定住户提交的事件
function  house_fun(obj){
	var houseid = $('.h_id').val();
	var yhid = $(obj).val();
	var sf = $(obj).siblings('.sf').val();
        if($(obj).is(':checked')){
            var type=1;
        }else{
            var type=0;
        }
	$.post("/xiaoqu/bind_yz",{houseid:houseid,yhid:yhid,sf:sf,type:type},function(msg){
		if(msg.status == 0){
			sweetAlert({title:'',text: msg.info,type: "warning",timer:2000,showConfirmButton: false});
		}
		if(msg.status == 1){}


	});
}
$('#rightside_zhuhu .right_quanxuan').click(function(){
    if($(this).is(':checked')){
        $(this).parents('.data-list').find('.bind_xq_y').prop('checked',true);
    }else{
        $(this).parents('.data-list').find('.bind_xq_y').prop('checked',false);
    }
});
var scrl = 0;
//绑定住户列表
$('#rightside_zhuhu .xq_li span,#rightside_zhuhu .xq_li>label>a').click(function(){
	var xiaoquid = $(this).attr('data-id');
	var right_sf = $('#rightside_zhuhu').attr('data-right_sf');
	var fun = $('#rightside_zhuhu').attr('data-house_fun');
	var right_input = $('#rightside_zhuhu').attr('data-right_input');
        if(right_input==1){
            var input_zhuhu = 'checkbox';
        }
        else{
            var input_zhuhu = 'radio';
        }

	if(xiaoquid != ''){
            if($(this).parents('li').find('.house_input').size()>0){

            }else{
		$.post("/xiaoqu/get_yz_list",{xiaoquid:xiaoquid},function(msg){
			var html = '';
			if(msg.status == 0){
				html+='<li>';
				html+='<label>';
				html+='<a href="javascript:">';
				html+=msg.data;
				html+='</a>';
				html+='</label>';
				html+='</li>';
				$('.yz_'+xiaoquid).html(html)
			}
			if(msg.status == 1){
				var data = msg.data;
				for(var i in data){
					html+='<li>';
					html+='<label>';
					html+='<input type="'+input_zhuhu+'" class="wxq-'+input_zhuhu+'  house_input" name="zh[]" value="'+data[i].id+'" onclick="'+fun+'">';

					html+=' '+data[i].name+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+data[i].phone;

					if(right_sf == 1){

						html+='<select class="bind-zhuhu pull-right sf" data-id="'+data[i].id+'" >';
						html+='<option value="1">业主本人</option>';
						html+='<option value="3">租客</option>';
						html+='<option value="2">亲属</option>';
						html+='<option value="4">其他</option>';
						html+='</select>';
					}
					html+='</label>';
					html+='</li>';
				}
				$('.yz_'+xiaoquid).html(html);
                                scrl = 1;
			}
		});
            }
	}

});

               $("#rightside_zhuhu .r-content").scroll(function () {

                    var scrollTop = $(this).scrollTop()+115;
                    var scrollHeight = $('.r-content').find('.data-list').height();
                    var windowHeight = $(this).height();
                    var _ul = $('.tree-child');
                    var right_input = $('#rightside_zhuhu').attr('data-right_input');
                    if(right_input==1){
                        var input_zhuhu = 'checkbox';
                    }
                    else{
                        var input_zhuhu = 'radio';
                    }

                    if (scrollTop + windowHeight >= scrollHeight) {
                        var xiaoquid = $('.x_id').val();
                        var right_sf = $('#rightside_zhuhu').attr('data-right_sf');
                        var fun = $('#rightside_zhuhu').attr('data-house_fun');
                        var last = $('.li_'+xiaoquid).find('input').last().val();
                        if(scrl){
                            scrl = 0;

                        $.post("/xiaoqu/get_yz_list",{xiaoquid:xiaoquid,last:last},function(msg){
			var html = '';
			if(msg.status == 0){

			}
			if(msg.status == 1){
				var data = msg.data;
				for(var i in data){
					html+='<li>';
					html+='<label>';
					html+='<input type="'+input_zhuhu+'" class="wxq-'+input_zhuhu+' house_input" name="zh[]" value="'+data[i].id+'" onclick="'+fun+'">';

					html+=' '+data[i].name+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+data[i].phone+'</span>>';

					if(right_sf == 1){

						html+='<select class="bind-zhuhu pull-right sf" data-id="'+data[i].id+'" >';
						html+='<option value="1">业主本人</option>';
						html+='<option value="3">租客</option>';
						html+='<option value="2">亲属</option>';
						html+='<option value="4">其他</option>';
						html+='</select>';
					}
					html+='</label>';
					html+='</li>';
				}
				$('.yz_'+xiaoquid).append(html);
                                scrl = 1;
			}
		});
            }

                    }
            	});
//绑定住户搜索
$('#rightside_zhuhu #se_k').keyup(function(){
	var k = $(this).val();
	var fun = $('#rightside_zhuhu').attr('data-house_fun');
	var t = $(this);
	var xiaoquid = $('.x_id').val();
	var right_sf = $('#rightside_zhuhu').attr('data-right_sf');
	var html = "";
        var right_input = $('#rightside_zhuhu').attr('data-right_input');
        if(right_input==1){
            var input_zhuhu = 'checkbox';
        }
        else{
            var input_zhuhu = 'radio';
        }
	if(k==''){
		$(this).parents('.rightside').find('.search-result').hide();
		$(this).parents('.rightside').find('.data-list').show();
	}else{
		var str = '';
		$.post("/xiaoqu/search_zhuhu",{k:k,xiaoquid:xiaoquid},function(msg){
			if(msg.status == 0){
				str+='<li><label class="rem">'+msg.data+'</label></li>';
			}
			if(msg.status == 1){
				var data = msg.data;
				for(var i in data){
					str+='<li><label class="rem"><input type="'+input_zhuhu+'" class="wxq-'+input_zhuhu+' house_input" name="zh[]" value="'+data[i].id+'" onclick="'+fun+'"> '+data[i].name+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+data[i].phone+'';
					if(right_sf == 1){

						str+='<select class="bind-zhuhu pull-right sf" data-id="'+data[i].id+'" >';
						str+='<option value="1">业主本人</option>';
						str+='<option value="3">租客</option>';
						str+='<option value="2">亲属</option>';
						str+='<option value="4">其他</option>';
						str+='</select>';
					}
					str+='</label></li>';
				}
			}
			t.parents('.rightside').find('.data-list').hide();
			t.parents('.rightside').find('.search-result').show().html(str);
		});
	}

});


//车辆绑定住户事件
function  car_fun(obj){
	var yhid = $(obj).val();
	var carid = $('.h_id').val();
        if($(obj).is(':checked')){
            var type=1;
        }else{
            var type=0;
        }
	$.post("/car/bind_yz",{carid:carid,yhid:yhid,type:type},function(msg){
		if(msg.status == 0){
			sweetAlert({title:'',text: msg.info,type: "warning",timer:2000,showConfirmButton: false});
		}
		if(msg.status == 1){}


	});
}
//获取车位
$('#rightside_chewei').on('click','.data-list span,.data-list a',function(){
	var xiaoquid=$(this).closest('li').attr('data-xiaoquid');
	var cw_is_checkbox=$(this).closest('li').attr('data-cw_is_checkbox');
	var that=$(this);
    var  input_check  = that.parents('li').find('.cw_input').is(':checked');
	if($(this).closest('li').find('span').attr('class') == 'add'){
		$.post(
			"/car/get_xq_cw",
			{xiaoquid:xiaoquid},
			function(msg){
				var str='';
				if(msg.status == 1){
					$(msg.data).each(function(){
						str+=' <li><label>';
						if(cw_is_checkbox == 1){
							if(input_check == true){
								var s = "checked=check";
							}
                            if(input_check != true){
                                s = '';
                            }
                            str+='<input type="checkbox" name="place_id" '+s+' value="'+this.place_id+'" data-cid="'+this.place_id+'" class="wxq-checkbox place" data-xiaoquid="'+xiaoquid+'" data-name="'+this.place_num+'" data-xqname="'+this.xiaoquname+'">';
						}else{
							str+='<input type="radio" name="place_id" value="'+this.place_id+'" class="place" data-xiaoquid="'+xiaoquid+'" data-name="'+this.place_num+'" data-xqname="'+this.xiaoquname+'">';
						}
						str+=' '+this.place_num+'</label></li>';
					});

				}
				if(msg.status == 0){
					str+=' <li><label><a href="javascript:">'+msg.info+'</a></label></li>';
				}
				that.parents('li').find('.tree-child').html(str);
			}
		);
	}
});

//车位搜索框
$('#rightside_chewei #se_k').keyup(function(){
	var kw=$(this).val();
	var cw_is_checkbox=$(this).attr('data-cw_is_checkbox');
	if(cw_is_checkbox == 1){
		var type = 'checkbox';
		var type_text = 'wxq-checkbox place';
	}else{
        var type = 'radio';
        var type_text = 'place';
	}
	var that=$(this);
	if(kw == ''){
		$(this).parents('.rightside').find('.search-result').hide();
		$(this).parents('.rightside').find('.data-list').show();
	}else{
		$.post(
			"/car/search_cw",
			{kw:kw},
			function(msg){
				var str='';
				$(msg).each(function(){
					str+= '<li><label class="rem">';
					str+='<input type="'+type+'" name="place_id" value="'+this.place_id+'" class="'+type_text+'" data-xiaoquid="'+this.xiaoquid+'" data-name="'+this.place_num+'" data-xqname="'+this.xiaoquname+'">';
					str+=' '+this.xiaoquname+'--'+this.place_num+'</label></li>';

				});
				that.parents('.rightside').find('.data-list').hide();
				that.parents('.rightside').find('.search-result').show().html(str);
			}
		);
	}
});

//模板关联车辆
$('#rightside_cheliang').on('click','.data-list span,.data-list a',function(){
	var xiaoquid=$(this).closest('li').attr('data-xiaoquid');
	var car_is_checkbox=$(this).closest('li').attr('data-car_is_checkbox');
	var that=$(this);
	if($(this).parents('li').find('ul li').length == 0){
		$.post(
			"/charge/get_xq_car",
			{xiaoquid:xiaoquid},
			function(msg){
				var str='';
				if(msg.status == 1){
					$(msg.data).each(function(){
						str+=' <li><label>';
						if(car_is_checkbox == 1){
							str+='<input type="checkbox"  name="xqcar_id" class="wxq-checkbox xqcar_id" value="'+this.xqcar_id+'" data-xiaoquid="'+this.xiaoquid+'">';
						}else{

                                                        str+='<input type="radio"  name="xqcar_id" class="wxq-radio xqcar_id" value="'+this.xqcar_id+'" data-xiaoquid="'+this.xiaoquid+'">';
						}
						str+=' '+this.place_num+'--'+this.car_num+'</label></li>';
					});

				}
				if(msg.status == 0){
					str+=' <li><label><a href="javascript:">'+msg.info+'</a></label></li>';
				}
				that.parents('li').find('.tree-child').html(str);
			}
		);
	}
});

//模板关联车辆搜索
$('#rightside_cheliang .se_k').keyup(function(){
	var kw=$(this).val();
	var car_is_checkbox=$(this).attr('data-car_is_checkbox');
	var that=$(this);
	if(kw == ''){
		$(this).parents('.rightside').find('.search-result').hide();
		$(this).parents('.rightside').find('.data-list').show();
	}else{
		$.post(
			'/charge/search_xq_car',
			{kw:kw},
			function(msg){
				var str='';
				$(msg).each(function(){
					str+= '<li><label class="rem">';
					if(car_is_checkbox == 1){
						str+='<input type="checkbox" data-xiaoquid="'+this.xiaoquid+'" name="xqcar_id" class="wxq-checkbox xqcar_id" value="'+this.xqcar_id+'">';
					}else{

                                                str+='<input type="radio" data-xiaoquid="'+this.xiaoquid+'"  name="xqcar_id" class="wxq-radio xqcar_id" value="'+this.xqcar_id+'">';
					}
					str += ' '+this.xiaoquname+'--'+this.place_num+'--'+this.car_num+'</label></li>';
				});
				that.parents('.rightside').find('.data-list').hide();
				that.parents('.rightside').find('.search-result').show().html(str);
			}
		);
	}
});

$('#rightside_fangwu .data-list span,#rightside_fangwu .get_b').click(function(){
    var con = $('#rightside_fangwu .xq_ul_'+$(this).attr('data-xqid'));
    if(con.find('a').size()>0){

    }else{
        var xid = $(this).attr('data-xqid');
        //获取楼宇数据
        $.post('/xiaoqu/ajax_building',{
            xiaoquid:xid
        },function(msg){
            if(msg.status == 0){
                con.html('<li><label><a href="javascript:"> '+msg.info+'</a></label>');
            }
            if(msg.status == 1){
                var str = '';
                var sss = '';
                var i = 0;
                if($('.input_xq_'+xid).is(':checked')){
                         sss = 'checked';
                    }else{
                        sss = '';
                    }
                var b_input = con.parents('#rightside_fangwu').attr('data-b-input');
                var b_fw = con.parents('#rightside_fangwu').attr('data-dy');

                for(i in msg.data){

                    str += '<li>';
                    if(b_fw!=2){
                        str += '<span  data-bid="'+msg.data[i].buildingid+'"  class="add"></span>';
                    }
                    str += '<label>';
                    if(b_input==1){
                        str += '<input '+sss+' type="checkbox" class="wxq-checkbox fw_input_2 input_b_'+msg.data[i].buildingid+'" value="'+msg.data[i].buildingid+'" />';
                    }
                    str +='<a data-bid="'+msg.data[i].buildingid+'" class="';
                    if(b_fw!=2){
                        str +=  'get_house';
                    }
                    str +='" href="javascript:">'+msg.data[i].buildingname+'</a></label><ul class="tree-child b_ul_'+msg.data[i].buildingid+'"></ul></li>';
                }
                con.html(str);
            }
        },'json');
    }
});
$(document).on('click','#rightside_fangwu .fw_2 span,#rightside_fangwu .get_house',function(){
    var con = $('#rightside_fangwu .b_ul_'+$(this).attr('data-bid'));
    if(con.find('a').size()>0){

    }else{
        var bid = $(this).attr('data-bid');
        //获取住户数据
        $.post('/xiaoqu/get_fh',{
            bid:bid,
            type:$('#rightside_fangwu').attr('data-dy')

        },function(msg){
            if(msg.status == 0){
                con.html('<li><label><a href="javascript:"> '+msg.info+'</a></label>');
            }
            if(msg.status == 1){
                var str = '';
                var sss = '';
                var i = 0;
                if($('.input_b_'+bid).is(':checked')){
                         sss = 'checked';
                    }else{
                        sss = '';
                    }
                    var xuanz = 'checkbox';
                    if(con.parents('#rightside_fangwu').attr('data-xuanz')==1){
                        xuanz = 'radio';
                    }
                    var sele = con.parents('#rightside_fangwu').attr('data-select');
                for(i in msg.data){
                    str += '<li><label><input name="houseid[]" data-xiaoquid="'+msg.data[i].xiaoquid+'" data-buildingid="'+msg.data[i].buildingid+'" data-unitid="'+msg.data[i].unitid+'" data-name="'+msg.data[i].houseno+'" type="'+xuanz+'" class="wxq-'+xuanz+' fw_input_3" '+sss+'  value="'+msg.data[i].houseid+'"> '+msg.data[i].houseno;
                    if(sele==1){
                           str += '<select name="" class="bind-zhuhu pull-right">'+
                                         '<option value="1">业主本人</option>'+
                                         '<option value="3">租客</option>'+
                                         '<option value="2">亲属</option>'+
                                         '<option value="4">其他</option>'+
                                     '</select> ';
                         }
                        str += '</label></li>';
                }
                con.html(str);
            }
        },'json');
    }

});


//模板关联车辆搜索
$('#rightside_fangwu .se_k').keyup(function(){
	var kw=$(this).val();
	var ser = $(this);
	if(kw == ''){
		$(this).parents('.rightside').find('.search-result').hide();
		$(this).parents('.rightside').find('.data-list').show();
	}else{
		$.post(
			'/xiaoqu/get_fanghao_key',
			{
                            kw:kw,
                            xiaoquid:$('#rightside_fangwu').attr('data-xiaoquid')
                        },
			function(msg){
                            var sss = '';
                            var xuanz = 'checkbox';
                            if(ser.parents('#rightside_fangwu').attr('data-xuanz')==1){
                                xuanz = 'radio';
                            }
				if(msg.status==0){
                                    sss = '<li><label class="rem"><a href="javascript:">'+msg.info+'</a></label></li>';
                                }
				if(msg.status==1){
                                    var sele = ser.parents('#rightside_fangwu').attr('data-select');
                                    for(i in msg.data){
                                        sss += '<li><label class="rem"><input data-xiaoquid="'+msg.data[i].xiaoquid+'" data-buildingid="'+msg.data[i].buildingid+'" data-unitid="'+msg.data[i].unitid+'" data-name="'+msg.data[i].houseno+'" type="'+xuanz+'" name="houseid[]" value="'+msg.data[i].houseid+'" class="wxq-'+xuanz+' fw_input_3"> '+msg.data[i].xiaoquname+'-'+msg.data[i].buildingname+'-'+msg.data[i].houseno;
                                        if(sele==1){
                                        sss += '<select name="" class="bind-zhuhu pull-right">'+
                                                      '<option value="1">业主本人</option>'+
                                                      '<option value="3">租客</option>'+
                                                      '<option value="2">亲属</option>'+
                                                      '<option value="4">其他</option>'+
                                                  '</select> ';
                                      }
                                      sss += '</label></li>';

                                }
                                }

                                ser.parents('.rightside').find('.data-list').hide();
                                ser.parents('.rightside').find('.search-result').html(sss).show();


			}
		);
	}
});
$(document).on('keyup','#formula #se_k',function(){

    var kkey = $(this).val();

    if(kkey!=''){
        $('#formula .data-list li').hide();
        $('#formula .data-list li').find('label').each(function(){
            if($(this).text().indexOf(kkey)!=-1){
                $(this).parents('li').show();
            }
        });
    }else{
        $('#formula .data-list li').show();
    }
});


$('#rightside_building_yh').on('click', '.xq_li span,.xq_li li>label>a,.xq_li .wxq-checkbox', function () {
	var ty = $(this).closest('ul').attr('data-ty');
	if (ty == 'xq') { //获取楼宇
		var xiaoquid = $(this).closest('li').attr('data-id');
		if($(this).closest('li').find('.b-li>li').length < 1){
			$.post(
				"/wuye/get_building_by_xiaoquid",
				{
					xiaoquid:xiaoquid
				},
				function(msg){
					if(msg.status == 1){
						var html = '';
						$(msg.data).each(function(){
							html+='<li data-id="'+this.buildingid+'"><span class="add"></span>';
							html+=' <label><input type="checkbox" class="wxq-checkbox building-item" value="'+this.buildingid+'"/><a href="javascript:">'+this.buildingname+'</a></label>';
							html+='<ul class="tree-child yh-li yh_'+this.buildingid+'"></ul></li>';
						})
						$('.building_'+xiaoquid+'').html(html);
					}
					if(msg.status == 0){
						$('.building_'+xiaoquid+'').html('<li><label><a href="javascript:">暂无相关数据</a></label></li>');
					}
				}
			)
		}
	}
	if(ty == 'ly'){ //获取住户
		var buildingid = $(this).closest('li').attr('data-id');
		var typ=$(this).attr('type');
		if($(this).closest('li').find('.yh-li>li').length < 1){
			$.post(
				"/wuye/get_yh_by_buildingid",
				{
					buildingid:buildingid
				},
				function(msg){
					if(msg.status == 1){
						var html = '';
						$(msg.data).each(function(){
							html+='<li data-id="'+this.id+'">';
							if(typ == 'checkbox'){
								html+=' <label><input type="checkbox" class="wxq-checkbox yh-item" checked value="'+this.id+'"/><a href="javascript:">'+this.name+'</a></label>';
							}else{
								html+=' <label><input type="checkbox" class="wxq-checkbox yh-item" value="'+this.id+'"/><a href="javascript:">'+this.name+'</a></label>';
							}
							html+='</li>';
						})
						$('.yh_'+buildingid+'').html(html);
					}
					if(msg.status == 0){
						$('.yh_'+buildingid+'').html('<li><label><a href="javascript:">暂无相关数据</a></label></li>');
					}
				}
			)
		}
	}
})

$('#rightside_building_yh #se_k').keyup(function(){
	var kw = $(this).val();
	if(kw != ''){
		$('#rightside_building_yh .xq_li').hide();
		$('#rightside_building_yh .search_li').show();
		$.post(
			"/wuye/get_yh",
			{
				kw:kw
			},
			function(msg){
				if(msg.status == 1){
					var html = '';
					$(msg.data).each(function(){
						html+='<li class="xq-item" data-id="'+this.id+'">';
						html+=' <label><input type="checkbox" class="wxq-checkbox yh-item" value="'+this.id+'"/><a href="javascript:">'+ this.xiaoquname+' '+this.name+' '+this.phone+'</a></label>';
						html+='</li>';
					})
					$('.search_li').html(html);
				}
			}
		)
	}else{
		$('#rightside_building_yh .xq_li').show();
		$('#rightside_building_yh .search_li').hide().empty();
	}
})
