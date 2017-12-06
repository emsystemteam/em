$(function(){
    function fixedLeft(){
        $(document).scroll(function(){
            var scrollTop = $(this).scrollTop();
            if(scrollTop > 156){
                $('.left-col').addClass('tofixed');
            }else{
                $('.left-col').removeClass('tofixed');
            }
        });
    }
    
     function pageOps(){
    
        $('.page-opts').on('click','.up',function(){
            $('html,body').animate({
                scrollTop:0
            },1000)
        });

        $('.page-opts').on('click','.down',function(){
            var domHeight = $('#content').height();

           
            $('html,body').animate({
                scrollTop:domHeight
            },1000)
        })
    }
    
    pageOps();
    
    fixedLeft();

    function addTopics(type){
        var topicsItem = '';
        var dataType = '';
        var choiceDom ='';
        var topicsName = '';                   
        var qsLen = $('.topics-box .question-item').size();   

        switch(type)
        {
            case 'topics-radio':
                dataType = 'topics-radio'; 
                topicsName = 'Q'+(ppp+1)+' 单选题';
                topicsType = '1';
                choiceDom = '<ul class="question-choice">'+
                        '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项1</label><span></span></div></div></div>'+
                        '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li>'+
                        '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项2</label><span></span></div></div></div>'+
                        '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li></ul>';
            break;

            case 'topics-checkbox':
                dataType = 'topics-checkbox'; 
                topicsName = 'Q'+(ppp+1)+' 多选题';
                topicsType = '2';
                choiceDom = '<ul class="question-choice">'+
                        '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项1</label><span></span></div></div></div>'+
                        '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li></li>'+
                        '<li><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项2</label><span></span></div></div></div>'+
                        '<input name="list['+ppp+'][]" type="hidden" class="se_an"  value=""></li></ul>';
            break;

            case 'topics-blank':
                dataType = 'topics-blank'; 
                topicsName = 'Q'+(ppp+1)+' 填空题';
                topicsType = '3';
                choiceDom = '<ul class="question-choice"><li><div class="choice-item"><input name="" readonly type="text" class="form-control-k"></div></li></ul>';
            break;
        }
        topicsItem +='<div class="question-item" data-ppp="'+ppp+'" data-type="'+dataType+'"><div  class="question-title">'; 
        topicsItem +='<div class="qs-title">'+topicsName+''+
                '</div><div class="topics-desc"></div><input name="timu['+ppp+'][question_titile]" type="hidden"  value="'+topicsName+'" class="se_timu" />'+
                '<input name="timu['+ppp+'][question_type]" value="'+topicsType+'" type="hidden"  class="se_type" />'+
                '<input name="timu['+ppp+'][question_desc]" value="" type="hidden"  class="se_desc" /><input name="timu['+ppp+'][show_num]" type="hidden" class="se_show_num" value="1">' +
                '<input name="timu['+ppp+'][min_item]"  type="hidden" class="se_min_item" value="1">'+
                '<input name="timu['+ppp+'][max_item]" type="hidden" class="se_max_item" value="2">'+
            '</div>'+choiceDom+'<div class="question-operate">';
        topicsItem +='<ul><li title="移动" class="qs-move"><span>移动<span></li><li title="操作"  data-shownum="1" data-min="1" data-max="2" data-type="'+topicsType+'" class="qs-handle"><span>操作<span></li>';
        topicsItem +='<li title="删除" class="qs-delete"><span>删除<span></li></ul></div></div>';
        $('.topics-box').append(topicsItem);  
        $('.topics-init').addClass('none');  
        $('html,body').animate({
            scrollTop:$('.topics-box').height()
        },500);
        
        $('.qs-handle').trigger("click");
    };
    
//    function qsIndex(){ //每道题的编号
//        $('.topics-box .question-item').each(function(i){
//            $(this).find('.qs-index').text('Q'+i+1); 
//        })   
//    }

    function qsMove(){ //题目拖动排序
        $( ".topics-box" ).sortable({                    
            placeholder: "ui-state-highlight",
            handle: ".qs-move",
            start:function(e){
                $('.ui-state-highlight').css('height',$(e.toElement).parents('.question-item').css('height'));
            },
            update:function(){                           
//                qsIndex();     
            }
        });
//        $( ".topics-box" ).disableSelection();                    
    }                

    function closeUpImg(){
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        $('#upload').removeClass('in').attr('aria-hidden',true).css('display','none'); 

        $('.yulan').hide();
        $('.that_i').val('');
        $('.yulan').find('img').attr('src','');
    }

    $('.topics-type li').click(function(){  //添加题
        var _class =  $(this).attr('class');
        addTopics(_class);
        ppp++;
        
    });

    $(document).on('click','.qs-delete',function(){ //删除题

        $(this).parents('.question-item').remove(); 
    });

    $(document).on('click','.qs-copy',function(){ //复制题

        $(this).parents('.question-item').removeClass('setting');
        $(this).parents('.question-operate').next('.question-setting').remove();                     
        $(this).parents('.question-item').after($(this).parents('.question-item').clone()); 

    });

    qsMove(); 
    
    function maxChoice(i){ //[[每道题的选项数]]
        var _option = '';       
        for(let j = 1;j<=i;j++){
            _option += '<option>'+j+'</option>';     
        }       
        $('.max-choice select').html(_option); 
        $('.max-choice:nth-of-type(2) option:last-child').attr('selected',true);
    }
    
    $(document).on('change','.col-show',function(){
        var _val = parseInt($(this).val());
        $(this).parents('.question-item').find('.question-choice li').css({
            'width':100/_val-1+'%',
            'display':'inline-block'
        })
    })
    
    
    
    $(document).on('click','.qs-handle',function(){ //编辑题
        var _type = $(this).parents('.question-item').attr('data-type');
        var choice_item = $(this).parents('.question-item').find('.question-choice').children('li');
        var qs_title = $(this).parents('.question-item').find('.qs-title').text();
        var qs_desc = $(this).parents('.question-item').find('.topics-desc').text();
        var setBox = '';
            setBox += '<div class="question-setting"><div class="set-qs-title"><label for="">问题标题：</label><div>';
            setBox += '<input type="text" class="form-control-k qs_title_input" value="'+qs_title+'"></div></div><div class="set-qs-title m-t-15">';
            setBox += '<label for="">问题描述：</label><div><input type="text" placeholder="问题描述" class="form-control-k i_desc" value="'+qs_desc+'"></div></div>';
            
            if(_type == 'topics-radio' || _type == 'topics-checkbox'){ //单选或多选，选项列表
                
                setBox +='<div class="set-qs-choice"><table><thead><tr><th width="20%">选项文字</th><th width="30%">选项说明</th><th width="30%">图片';
                setBox +='</th><th width="30%">操作</th></tr></thead><tbody>';
                for(var i=0;i<choice_item.size();i++){
                    setBox +='<tr><td><input type="text" class="form-control-k choice_text_input" value="'+choice_item.eq(i).find('.choice-text label').text()+'">'; 
                     setBox +='</td><td class="p-l-10"><input class="form-control-k" value="'+choice_item.eq(i).find('.choice-text span').text()+'" placeholder="选项说明"></td><td>';
                    if(choice_item.eq(i).hasClass('has-img')){ //选项是否有图片
                        setBox += '<a class="choice-img uped"><i class="fa fa-photo"></i></a>';     
                    }else{
                        setBox += '<a class="choice-img"><i class="fa fa-photo"></i></a>';     
                    }
                    setBox +='</td><td><a href="javascript:" class="move-up" title="向上移动"><i class="fa fa-long-arrow-up"></i>';   
                    setBox +='</a><a href="javascript:" class="m-l-20 move-down" title="向下移动"><i class="fa fa-long-arrow-down"></i></a>';    
                    setBox +='<a href="javascript:" class="m-l-20 remove-choice" title="删除"><i class="fa fa-trash"></i></a></td></tr>';    
                }
                setBox +='<tr><td style="text-align: left;"><a href="javascript:" class="addChoice"><b class="fs-20">＋</b>添加选项</a></td>';
                setBox +='<td colspan="3" style="text-align: right;"><div class="set-qs-logic">';
                
                if(_type ==  'topics-checkbox'){
                    setBox += '<div class="max-choice"><label>最少选</label><select class="form-control-k qs_title_input min_input"></select>项</div>';
                    setBox += '<div class="max-choice"><label>最多选</label><select class="form-control-k qs_title_input max_input"></select>项</div>';
                }
                
                setBox +='<div><label>每行显示:</label><select class="form-control-k col-show qs_title_input qs_shounum_input">';
                for(let jj=1;jj<=10;jj++){
                    setBox += '<option>'+jj+'</option>';    
                }
                setBox +='</select>项</div>';                
                setBox +='</div>';
                setBox +='</td></tr></tbody></table>';
               
                
                setBox +='<button type="button" class="btn btn-sm btn-primary btn-w-m save-set m-t-15">保存</button></div></div>';                            
            }else{ // 填空题
                setBox +='<button type="button" class="btn btn-sm m-t-15 btn-primary btn-w-m save-set">保存</button></div>';     
            }                        


        if($(this).parents('.question-item').hasClass('setting')){  //取消题目设置
            $(this).parents('.question-item').removeClass('setting');
            $(this).parents('.question-item').children('.question-setting').remove(); 
        }else{ // 开始设置题
            $('.question-item').removeClass('setting');                        
            $('.topics-box .question-setting').remove();

            $(this).parents('.question-item').addClass('setting');
            $(this).parents('.question-item').append(setBox);
            maxChoice(choice_item.length);
            var itype = $(this).attr('data-type');
            var max_item = parseInt($(this).attr('data-max'));
            var min_item = parseInt($(this).attr('data-min'));
            var show_num = parseInt($(this).attr('data-shownum'));
            if(itype == 1){
                $(this).parents('.question-item').find('.qs_shounum_input option').eq(show_num-1).attr('selected',true);
            }
            if(itype == 2){
                $(this).parents('.question-item').find('.max_input option').eq(max_item-1).attr('selected',true);
                $(this).parents('.question-item').find('.min_input option').eq(min_item-1).attr('selected',true);
                $(this).parents('.question-item').find('.qs_shounum_input option').eq(show_num-1).attr('selected',true);
            }
        }                    
    });

    $(document).on('click','.addChoice',function(){  //添加选项
        var iii = $(this).parents('.question-setting').siblings('.question-choice').children('li').length;
        $(this).parents('.question-item').find('.se_min_item').val(1);
        var jj = iii+1;
        $(this).parents('.question-item').find('.se_max_item').val(jj);
        var _col = parseInt($(this).parents('.question-item').find('.col-show').val());
        var _style = 'style="display:inline-block;width:'+(100/_col-1)+'%;"';
        var _tr = '';
        var _li = '';
            _tr +='<tr><td><input type="text" class="form-control-k choice_text_input" placeholder="选项文字"></td>';
            _tr +='<td class="p-l-10"><input type="text" class="form-control-k" placeholder="选项说明"></td>';
            _tr +='<td><a class="choice-img"><i class="fa fa-photo"></i></a></td><td>';
            _tr +='<a href="javascript:" class="move-up" title="向上移动"><i class="fa fa-long-arrow-up"></i></a>';
            _tr +='<a href="javascript:" class="m-l-20 move-down" title="向下移动"><i class="fa fa-long-arrow-down"></i></a>';
            _tr +='<a href="javascript:" class="m-l-20 remove-choice" title="删除"><i class="fa fa-trash"></i></a></td></tr>';
            
            _li +='<li '+_style+'><div class="choice-info"><i class="choice-icon"></i><div class="choice-item"><div class="choice-text"><label>选项文字</label><span><span></div></div></div>'+
                    '<input name="list['+$(this).parents('.question-item').attr('data-ppp')+'][]" type="hidden" class="se_an" value=""></li>';
        $(this).parents('tr').before(_tr);
        $(this).parents('.question-setting').siblings('.question-choice').append(_li);
        maxChoice($(this).parents('.question-setting').siblings('.question-choice').children('li').length)
    });

    $(document).on('keyup','.qs_title_input',function(){ //设置题的题目
        $(this).parents('.question-item').find('.qs-title').text($(this).val());
        $(this).parents('.question-item').find('.se_timu').val($(this).val());
    });
    $(document).on('keyup','.i_desc',function(){ //设置题的题目描述
        
        $(this).parents('.question-item').find('.topics-desc').text($(this).val());
        $(this).parents('.question-item').find('.se_desc').val($(this).val());
    });

    $(document).on('change','.qs_shounum_input',function(){ //设置选项行数
        $(this).parents('.question-item').find('.se_show_num').val($(this).find('option:selected').val());
        $(this).parents('.question-item').find('.qs-handle').attr('data-shownum',$(this).find('option:selected').val());
    });

    $(document).on('change','.min_input',function(){ //
        $(this).parents('.question-item').find('.se_min_item').val($(this).find('option:selected').val());
        $(this).parents('.question-item').find('.qs-handle').attr('data-min',$(this).find('option:selected').val());

    });

    $(document).on('change','.max_input',function(){ //
        $(this).parents('.question-item').find('.se_max_item').val($(this).find('option:selected').val());
        $(this).parents('.question-item').find('.qs-handle').attr('data-max',$(this).find('option:selected').val());
    });

    $(document).on('keyup','.choice_text_input',function(){ //设置选项的文字
        var _i = $(this).parents('tr').index();
        if($(this).val() != ''){
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text label').text($(this).val());
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.se_an').val($(this).val());
        }else{
            $(this).val('');
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text label').text($(this).val());
        }                    
    });
    
    $(document).on('keyup','td.p-l-10>input',function(){ //设置选项的说明
        var _i = $(this).parents('tr').index();
        if($(this).val() != ''){
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text span').text($(this).val());     
        }else{
            $(this).val('');
            $(this).parents('.question-item').find('.question-choice li').eq(_i).find('.choice-text span').text($(this).val());
        }                    
    });

    $(document).on('click','.remove-choice',function(){ // 删除选项
        var _tr = $(this).parents('tr');
        
        maxChoice($(this).parents('.question-setting').siblings('.question-choice').children('li').length-1)
        $(this).parents('.question-item').children('.question-choice').children('li').eq(_tr.index()).remove();
        _tr.remove();       
       
    });

    $(document).on('click','.move-up',function(){ // 上移选项
        var thisTr = $(this).parents('tr');
        var thisChoice = $(this).parents('.question-item').children('.question-choice').children('li').eq(thisTr.index());
        if(thisTr.index() == 0){
            alert('已经是最顶部了');
        }else{
            thisTr.insertBefore(thisTr.prev('tr'));
            thisChoice.insertBefore(thisChoice.prev('li'));    
        }                                    
    });

    $(document).on('click','.move-down',function(){ // 下移选项
        var thisTr = $(this).parents('tr');
        var thisChoice = $(this).parents('.question-item').children('.question-choice').children('li').eq(thisTr.index());

        if(thisTr.index() == parseInt($(this).parents('tbody').children('tr').size()-2)){ 
            alert('已经是最底部了');
        }else{                        
            thisTr.insertAfter(thisTr.next('tr'));
            thisChoice.insertAfter(thisChoice.next('li'));       
        }

    });

    $(document).on('click','.choice-img',function(){ // 选项图片                   
        var that_i = $(this).parents('tr').index();

        $('.that_i').val(that_i);
        $('body').addClass('modal-open').append('<div class="modal-backdrop fade in"></div>');
        $('#upload').addClass('in').attr('aria-hidden',false).css('display','block');
    });

    $(document).on('click','.close-upimg',function(){ //取消上传
        closeUpImg();
    });

    
    $(document).on('click','.save-img',function(){  //保存图片

        var _i = parseInt($('.that_i').val());
        var _img = $('.yulan img').attr('src');                    

        if($('.setting').find('.question-setting tr').eq(_i+1).find('.choice-img').hasClass('uped')){
            $('.setting').find('.question-choice li').eq(_i).find('img').attr('src',_img);   
        }else{
            $('.setting').find('.question-choice li').eq(_i).addClass('has-img').children('.choice-info').before('<img src="'+_img+'"/>');    
        }

        $('.setting').find('.question-setting tr').eq(_i+1).find('.choice-img').addClass('uped');                    

        closeUpImg();
    });
    
    $(document).on('click','.save-set',function(){
        $(this).parents('.setting').find('.qs-handle').trigger('click');
    })

});  