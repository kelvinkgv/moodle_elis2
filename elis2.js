var block_elis2 = (function() {

    var elis2_act_meet_criteria = false;
    var elis2_count_chinese = false;

    return {
    	elis2InitConfigDND : function() {
        	YUI().use('dd-constrain', 'dd-proxy', 'dd-drop', function(Y) {
        	    //Listen for all drop:over events
        	    Y.DD.DDM.on('drop:over', function(e) {
        	        //Get a reference to our drag and drop nodes
        	        var drag = e.drag.get('node'),
        	            drop = e.drop.get('node');
        	 
        	        //Are we dropping on a tr node?
        	        if (drop.get('tagName').toLowerCase() === 'tr') {
        	            //Are we not going up?
        	            if (!goingUp) {
        	                drop = drop.get('nextSibling');
        	            }
        	            //Add the node to this list
        	            e.drop.get('node').get('parentNode').insertBefore(drag, drop);
        	            //Resize this nodes shim, so we can drop on it later.
        	            e.drop.sizeShim(); 
        	        }
        	    });
        	    //Listen for all drag:drag events
        	    Y.DD.DDM.on('drag:drag', function(e) {
        	        //Get the last y point
        	        var y = e.target.lastXY[1];
        	        //is it greater than the lastY var?
        	        if (y < lastY) {
        	            //We are going up
        	            goingUp = true;
        	        } else {
        	            //We are going down.
        	            goingUp = false;
        	        }
        	        //Cache for next check
        	        lastY = y;
        	    });
        	    //Listen for all drag:start events
        	    Y.DD.DDM.on('drag:start', function(e) {
        	        //Get our drag object
        	        var drag = e.target;
        	        //Set some styles here
        	        drag.get('node').setStyle('opacity', '.25');
        	        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
        	        drag.get('dragNode').setStyles({
        	            opacity: '.5',
        	            borderColor: drag.get('node').getStyle('borderColor'),
        	            backgroundColor: drag.get('node').getStyle('backgroundColor')
        	        });
        	    });
        	    //Listen for a drag:end events
        	    Y.DD.DDM.on('drag:end', function(e) {
        	        var drag = e.target;
        	        //Put our styles back
        	        drag.get('node').setStyles({
        	            visibility: '',
        	            opacity: '1'
        	        });
        	        var lis = Y.Node.all('#act_table tr');
        	        var ct = 0;
        	        lis.each(function(v, k) {
        	        	if(ct!=0){
        	        		var act_id = v.get('id');
        	        		
        	        		var tmp = act_id.split('_');
        	        		var act = tmp[1];
        	        		
        	        		Y.one('#'+act+'_order').set('value', k);
        	        	}
        	        	ct++;
        	        });
        	        
        	    });
        	    //Listen for all drag:drophit events
        	    Y.DD.DDM.on('drag:drophit', function(e) {
        	        var drop = e.drop.get('node'),
        	            drag = e.drag.get('node');
        	 
        	        //if we are not on an tr, we must have been dropped on a ul
        	        if (drop.get('tagName').toLowerCase() !== 'tr') {
        	            if (!drop.contains(drag)) {
        	                drop.appendChild(drag);
        	            }
        	        }
        	    });
        	 
        	    //Static Vars
        	    var goingUp = false, lastY = 0;
        	 
        	    //Get the list of tr's in the lists and make them draggable
        	    var lis = Y.Node.all('#act_table tr');
        	    lis.each(function(v, k) {
        	        var dd = new Y.DD.Drag({
        	            node: v,
        	            target: {
        	                padding: '0 0 0 20'
        	            }
        	        }).plug(Y.Plugin.DDProxy, {
        	            moveOnEnd: false
        	        }).plug(Y.Plugin.DDConstrained, {
        	            constrain2node: '#act_table'
        	        });
        	    });
        	 
        	    //Create simple targets for the 2 lists.
        	    var uls = Y.Node.all('#act_table tr');
        	    uls.each(function(v, k) {
        	        var tar = new Y.DD.Drop({
        	            node: v
        	        });
        	    });
        	 
        	});
        	
        },

        addBook2List: function(add_isbn,id,max_rate,blockid) {
        	
        		YUI().use('io','node', 'button', function(Y) {
        			var loading_img = '<img src="images/loading.gif">';
        			Y.one('#action_div_'+add_isbn).setContent(loading_img);
        			
        			var tr_ct = 0;
        			var cb = Y.all('.booklist_class');
        			cb.each(function (taskNode) {
        				tr_ct++;	
        			});
        			
        	        var data = { blockid:blockid,tr_ct:tr_ct,add_isbn:add_isbn,max_rate:max_rate , id:id,task:'add_book_2_list'};
        	        Y.io('ajax.php', {
        	            method: 'GET',
        	            data: data,
        	            on: {
        	                success: function (id, result) {
        	                	YUI().use('json-parse', 'json-stringify', function (Y) {
        	                		var json = Y.JSON.parse(result.responseText);
        	                		YUI().use('node', 'button', function(Y) {
        	                			Y.one('#action_div_'+add_isbn).setContent(json.return_html);
        	                			if(Y.one('#my_booklist_table')!=null)
        	                			Y.one('#my_booklist_table').prepend(json.new_record_html);
        	                			
        	                		});
        	                	});
        	                }
        	            }
        	        });
        		});
        	     	
        },

        removeBookFromList: function(delete_isbn,id,max_rate){
        	YUI().use('io', function (Y) {
        		
        			YUI().use('node', 'button', function(Y) {
        				if(Y.one('#action_div_'+delete_isbn)!=null){
        					var loading_img = '<img src="images/loading.gif">';
        					Y.one('#action_div_'+delete_isbn).setContent(loading_img);
        				}
        			});
        		
        		
                var data = { id:id,delete_isbn:delete_isbn,max_rate:max_rate, task:'remove_book_from_list'};
                Y.io('ajax.php', {
                	
                    method: 'GET',
                    data: data,
                    on: {
                        success: function (id, result) {
                        	YUI().use('json-parse', 'json-stringify', function (Y) {
                        		
        	                		var json = Y.JSON.parse(result.responseText);
        	                    	YUI().use('node', 'button', function(Y) {
        	                    		if(Y.one('#action_div_'+delete_isbn)!=null)
        	                    			Y.one('#action_div_'+delete_isbn).setContent(json.return_html);
        	                    		if(Y.one('#my_booklist_'+delete_isbn)!=null)
        	                    			Y.one('#my_booklist_'+delete_isbn).hide();
        	                    		if(Y.one('#my_booklist_'+delete_isbn+'_hr')!=null)
        	                    			Y.one('#my_booklist_'+delete_isbn+'_hr').hide();
        	                    		if(Y.one('#book_rating_div_'+delete_isbn)!=null)
        	                    			block_elis2.getBookAvgRating(delete_isbn,max_rate);
        	                		});
        	                    	
        	                    	
                        		
                        	});
                        }
                    }
                });
        	});
        },

        rateBook: function(score,isbn,max_rate,small_img){
        	YUI().use('io', function (Y) {
        		if(typeof(small_img)=='undefined')
        			small_img = 0;
                var data = { score:score, isbn:isbn, max_rate:max_rate, task:'rate_book',small_img:small_img};
                Y.io('ajax.php', {
                    method: 'GET',
                    data: data,
                    on: {
                        success: function (id, result) {
                        	YUI().use('json-parse', 'json-stringify', function (Y) {
                        		
                        		var json = Y.JSON.parse(result.responseText);
        	                    YUI().use('node', 'button', function(Y) {
        	                    	if(Y.one('#book_rating_div_'+isbn)!=null)
        	                    		Y.one('#book_rating_div_'+isbn).setContent(json.book_rating_div_html);
        	                		Y.one('#rate_div_'+isbn).setContent(json.return_html);
        	                	});
                        		
                        	});
                        }
                    }
                });
        	});
        },

        getBookAvgRating: function(isbn,max_rate){
        	YUI().use('io', function (Y) {
        		var data = { isbn:isbn, max_rate:max_rate, task:'get_book_avg_rating'};
                Y.io('ajax.php', {
                    method: 'GET',
                    data: data,
                    on: {
                        success: function (id, result) {
                        	YUI().use('json-parse', 'json-stringify', function (Y) {
                        		
                        		var json = Y.JSON.parse(result.responseText);
        	                    YUI().use('node', 'button', function(Y) {
        	                    	if(Y.one('#book_rating_div_'+isbn)!=null)
        	                    		Y.one('#book_rating_div_'+isbn).setContent(json.book_rating_div_html);
        	                	});
                        		
                        	});
                        }
                    }
                });
        	});
        },
        rateMouseOutEffect: function(score,isbn){
        	for(var i=1;i<=score;i++){
        		YUI().use('node', 'button', function(Y) {
            		Y.one('#rate_'+isbn+'_'+i).set('src','images/starOff.gif');
        		});
        	}	
        },

        rateMouseOverEffect: function(score,isbn){
        	for(var i=1;i<=score;i++){
        		YUI().use('node', 'button', function(Y) {
            		Y.one('#rate_'+isbn+'_'+i).set('src','images/starOn.gif');
        		});
        	}	
        },

        rateAgain: function(isbn,max_rate,small_img){
        	if(typeof(small_img)=='undefined')
        		small_img = 0;
        	YUI().use('node', 'button', function(Y) {
        		var loading_img = '<img src="images/loading.gif">';
        		Y.one('#rate_div_'+isbn).setContent(loading_img);
        	});
        	YUI().use('io', function (Y) {
                var data = { isbn:isbn, max_rate:max_rate, task:'remove_rate',small_img:small_img};
                Y.io('ajax.php', {
                    method: 'GET',
                    data: data,
                    on: {
                        success: function (id, result) {
                        	YUI().use('json-parse', 'json-stringify', function (Y) {
                        		
                        		var json = Y.JSON.parse(result.responseText);
        	                    YUI().use('node', 'button', function(Y) {
        	                    	if(Y.one('#book_rating_div_'+isbn)!=null)
        	                    		Y.one('#book_rating_div_'+isbn).setContent(json.book_rating_div_html);
        	                		Y.one('#rate_div_'+isbn).setContent(json.return_html);
        	                	});
                        		
                        	});
                        }
                    }
                });
        	});
        },

        /*function applyAllYear(act,checked){
        	YUI().use('node', 'button', function(Y) {
        		var cb = Y.all('.elis2_'+act+'_cb');
        		
        		cb.each(function (taskNode) {
        			
        			if(checked){
        				taskNode.set('checked',true);	
        			}else{
        				taskNode.set('checked',false);
        			}
        		});
        		
        		
        	});
        }*/

        bookRead: function(is_checked,isbn,ct,blockid){
        	YUI().use('node', 'button', function(Y) {
        		var loading_img = '<img src="images/loading.gif">';
        		Y.one('#activity_div_'+isbn).setContent(loading_img);
        	});
        	YUI().use('io', function (Y) {
        		var data = {ct:ct, isbn:isbn,blockid:blockid,read:is_checked==true?1:0, task:'update_read_status'};
        	    Y.io('ajax.php', {
        	        method: 'GET',
        	        data: data,
        	        on: {
        	            success: function (id, result) {
        	            	YUI().use('json-parse', 'json-stringify', function (Y) {
        	            		var json = Y.JSON.parse(result.responseText);
        	            		
        	                	YUI().use('node', 'button', function(Y) {
        	            			Y.one('#activity_div_'+isbn).setContent(json.return_html);
        	            		});
        	            	});
        	            }
        	        }
        	    });	
        	});
        },

        updateBuddy: function(buddy_no,bid){
        	YUI().use('io', function (Y) {
        		var data = { task:'update_buddy',buddy_no:buddy_no,bid:bid};
        	    Y.io('ajax.php', {
        	        method: 'POST',
        	        data: data,
        	        on: {
        	            success: function (id, result) {
        	            	YUI().use('json-parse', 'json-stringify', function (Y) {
        	            		var json = Y.JSON.parse(result.responseText);
        	            		
        	                	YUI().use('node', 'button', function(Y) {
        	            			//Y.one('#activity_div_'+isbn).setContent(json.return_html);
        	                		location.reload(); 
        	            		});
        	            	});
        	            }
        	        }
        	    });	
        	});
        },

        elis2OpenExplainDialog: function(ct,isbn,act_name,width,height,visible,header_caption,show_remove,blockid){
        	YUI().use('io', function (Y) {
        		var data = { isbn:isbn,act_name:act_name,blockid:blockid,task:'render_explain'};
        	    Y.io('ajax.php', {
        	        method: 'GET',
        	        data: data,
        	        on: {
        	            success: function (id, result) {
        	            	YUI().use('json-parse', 'json-stringify', function (Y) {
        	            		var json = Y.JSON.parse(result.responseText);
        	            		YUI().use('node', function(Y) {

        	            			Y.one('#explain_dialog_content').set('offsetHeight',height-50);
        	            			Y.one('#explain_dialog_content').setContent(json.return_html);
        	            			block_elis2.initDialog(header_caption,'explain_dialog',isbn,act_name,width,height,visible,show_remove,blockid);
        	            		});
        	            	});
        	            }
        	        }
        	    });
        	});
        },

        elis2OpenActDialog: function(ct,isbn,act_name,width,height,visible,header_caption,show_remove,blockid,gbook_id){
        	
        	YUI().use('io', function (Y) {
        		var data = { isbn:isbn,act_name:act_name,task:'render_act_form'};
        	    Y.io('ajax.php', {
        	        method: 'GET',
        	        data: data,
        	        on: {
        	            success: function (id, result) {
        	            	YUI().use('json-parse', 'json-stringify', function (Y) {
        	            		var json = Y.JSON.parse(result.responseText);
        	            		YUI().use('node', function(Y) {

        	            			Y.one('#'+act_name+'_dialog_content_'+ct).set('offsetHeight',height-50);
        	            			Y.one('#'+act_name+'_dialog_content_'+ct).setContent(json.return_html);
        	            			block_elis2.initDialog(header_caption,act_name+'_dialog_'+ct,isbn,act_name,width,height,visible,show_remove,blockid,gbook_id);

        	            			if(json.return_js!=''){
        		            			for(var i=0;i<json.return_js.length;i++){
        		            				eval(json.return_js[i]);
        		            			}
        	            			}
        	            		});
        	            	});
        	            }
        	        }
        	    });
        	});
        },

        initDialog: function(header_caption,element_id,isbn,header,width,height,visible,show_remove,blockid,gbook_id){
        	
        	YUI().use('panel','dd-plugin', function (Y) {
        	

        	    var panel, nestedPanel;
        	    
        		// Create the main modal form.
        	    panel = new Y.Panel({
        	        srcNode      : '#'+element_id,
        	        headerContent: header_caption,
        	        width        : width,
        	        height		 : height,
        	        monitorresize : true,
        	        zIndex       : 5,
        	        centered     : true,
        	        modal        : true,
        	        visible      : visible,
        	        render       : true
        	        
        	    });
        	    panel.plug(Y.Plugin.Drag,{handles:['.yui3-widget-hd']});
        	    //plugins      : [Y.Plugin.Drag]
        	    if(header_caption!='Explain'){
        		    panel.addButton({
        		        value  : 'Submit',
        		        section: Y.WidgetStdMod.FOOTER,
        		        classNames: "act_submit_btn",
        		        action : function (e) {
        		            e.preventDefault();
        		            elis2SubmitActivity(isbn,element_id,blockid);
        		        }
        		    });
        	    }
        	
        	    if(show_remove==1){
        		    panel.addButton({
        		        value  : 'Remove record',
        		        section: Y.WidgetStdMod.FOOTER,
        		        classNames: "act_remove_btn",
        		        action : function (e) {
        		            e.preventDefault();
        		            
        		            removeAllItemsConfirm(isbn,blockid,gbook_id);
        		        }
        		    });
        	    }
        	
        	    Y.all('.'+element_id+'_btn').on('click', function (e) {
        	        panel.show();
        	    });
        	    
        	    Y.all('.yui3-button-icon').on('click', function (e) {
        	    	elis2DestoryDialog();
        	    });
        	    
        	    function elis2SubmitActivity(isbn,element_id,blockid) {
        	    	if(elis2_act_meet_criteria == false){
        	    		alert('Criteria not met!');
        	    		return false;
        	    	}
        	    		
        	    	var qid 		= [];
        	    	var ans 		= [];
        	    	var submitted 	= [];
        	    	
        	    	YUI().use('io','node', 'button', function (Y) {
        	    		
        				//YUI().use('node', 'button', function(Y) {
        					
        					var input = Y.all('.elis2_act_ans');
        					var ct = 0;
        					input.each(function (taskNode) {
        						if(taskNode.get('type')=='checkbox'){
        							ans[ct]	= (taskNode.get('checked'))?taskNode.get('value'):0;
        						}
        						else{
        							ans[ct]		= taskNode.get('value');
        						}
        						qid[ct] 	= taskNode.get('id');
        						
        						if(Y.one('#submitted_'+taskNode.get('id'))!=null){
        							submitted[ct] = Y.one('#submitted_'+taskNode.get('id')).get('value');
        						}
        						ct++;
        					});
        				//});
        					
        				var obj = { qid:qid,ans:ans,submitted:submitted,isbn:isbn};
        				var data = { str:JSON.stringify(obj), task:'submit_activity',blockid:blockid};
        				
        		        Y.io('ajax.php', {
        		        	method: 'POST',
        		        	data: data,
        		        	
        		            on: {
        		                success: function (id, result) {
        		                	YUI().use('json-parse', 'json-stringify', function (Y) {
        			            		var json = Y.JSON.parse(result.responseText);
        			            		alert(json.return_html);
        			            		
        			            		if(json.result==1){
        			            			elis2DestoryDialog();
        			            			var tmp_arr = element_id.split('_');
        			            			var ct = tmp_arr[tmp_arr.length-1];
        			            			var act_type = tmp_arr[0];
        			            			elis2RefreshActIcon(isbn,ct,blockid);
        			            			elis2RefreshActSumIcon(act_type,1);
        			            			YUI().use('node', function(Y) {
            		            				Y.one('#delete_btn_'+isbn).setContent('');
                	                		});
        			            		}
        			            			
        			            	});
        		                }
        		            }
        		        });
        	    	});

        	    	
        	    }
        	    
        	    function elis2RefreshActSumIcon(act_type,mod){
        	    	YUI().use('node','json-parse', 'json-stringify', function (Y) {
	        	    	var html = Y.one('#'+act_type+'_icon_sum').get('innerHTML');
	        	    	
	        	    	if(mod==1)
	        	    		var total = parseInt(html)+1;
	        	    	else
	        	    		var total = parseInt(html)-1;
	        	    	
	        	    	Y.one('#'+act_type+'_icon_sum').setContent(total);
        	    	});
        	    }
        	    
        	    function elis2RefreshActIcon(isbn,ct,blockid){

        	    	YUI().use('io','node', 'button', function (Y) {
        				var data = { ct:ct,isbn:isbn,blockid:blockid,task:'get_act_icon'};
        		        Y.io('ajax.php', {
        		        	method: 'POST',
        		        	data: data,
        		        	
        		            on: {
        		                success: function (id, result) {
        		                	YUI().use('node','json-parse', 'json-stringify', function (Y) {
        			            		var json = Y.JSON.parse(result.responseText);
        			            		
        			            		if(Y.one('#activity_'+ct+'_p')!=null)
        		                			Y.one('#activity_'+ct+'_p').setContent(json.return_html);
        			            	});
        		                }
        		            }
        		        });
        	    	});
        	    }
        	    
        	
        	    // Define the elis2DestoryDialog function - this will be called when
        	    // 'Remove All Items' is pressed on the modal form and is confirmed 'yes'
        	    // by the nested panel.
        	    function elis2DestoryDialog() {
        	        
        	        panel.hide();
        	        panel.destroy();
        	        
        	        var ele_arr = element_id.split("_");
        	        if(Y.one('#activity_div_'+isbn))
        	        	Y.one('#activity_div_'+isbn).append('<div id="'+element_id+'" style="float:center;"><div style="overflow:auto;" id="'+ele_arr[0]+'_'+ele_arr[1]+'_content_'+ele_arr[2]+'"></div></div>');
        	        
        	       	Y.one('#explain_div').setContent('<div id="explain_dialog" style="float:center;"><div style="overflow:auto;" id="explain_dialog_content"></div></div>');
        	    }
        	    
        	    function elis2RemoveActivity(isbn,blockid,gbook_id){
        	    	var qid = [];
        	    	YUI().use('io','node', 'button', function (Y) {
        				var input = Y.all('.act_submitted_field');
        				
        				var ct = 0;
        				input.each(function (taskNode) {
        					
        					qid[ct]		= taskNode.get('value');
        					ct++;
        				});
        				
        				/*alert(input);
        				alert(ct);
        				alert(qid);*/
        				var obj = { qid:qid,isbn:isbn};
        				var data = { str:JSON.stringify(obj), task:'delete_activity'};
        				
        		        Y.io('ajax.php', {
        		        	method: 'POST',
        		        	data: data,
        		        	
        		            on: {
        		                success: function (id, result) {
        		                	YUI().use('json-parse', 'json-stringify', function (Y) {
        			            		var json = Y.JSON.parse(result.responseText);
        			            		alert(json.return_html);
        			            		var tmp_arr = element_id.split('_');
        		            			var ct = tmp_arr[tmp_arr.length-1];
        		            			var act_type = tmp_arr[0];
        		            			elis2RefreshActIcon(isbn,ct,blockid);
        		            			
        		            			if(json.delete_result){
	        		            			elis2RefreshActSumIcon(act_type,2);
	        		            			var delete_img = '<a href="javascript:block_elis2.removeBookFromList(\''+isbn+'\',\''+gbook_id+'\')"><img src="images/button-cross.png" title=""></a>';
	        		            			
	        		            			YUI().use('node', function(Y) {
	        		            				Y.one('#delete_btn_'+isbn).setContent(delete_img);
	            	                		});
        		                		}
        			            	});
        		                }
        		            }
        		        });
        	    	});
        	    }
        	    
        	    // Instantiate the nested panel if it doesn't exist, otherwise just show it.
        	    function removeAllItemsConfirm (isbn,blockid,gbook_id) {
        	        if (nestedPanel) {
        	            return nestedPanel.show();
        	        }
        	
        	        nestedPanel = new Y.Panel({
        	            bodyContent: 'Are you sure you want to remove all items?',
        	            width      : 400,
        	            zIndex     : 6,
        	            centered   : true,
        	            modal      : true,
        	            render     : '#nestedPanel',
        	            buttons: [
        	                {
        	                    value  : 'Yes',
        	                    section: Y.WidgetStdMod.FOOTER,
        	                    action : function (e) {
        	                        e.preventDefault();
        	                        nestedPanel.hide();
        	                        panel.hide();
        	                        elis2RemoveActivity(isbn,blockid,gbook_id);
        	                        elis2DestoryDialog();
        	                    }
        	                },
        	                {
        	                    value  : 'No',
        	                    section: Y.WidgetStdMod.FOOTER,
        	                    action : function (e) {
        	                        e.preventDefault();
        	                        nestedPanel.hide();
        	                    }
        	                }
        	            ]
        	        });
        	    }
        	});
        },

        elis2CalculateChiWord: function(jsText){
        	
        	if (jsText == '' || jsText == null) {
        		return 0;
        	}
        	
        	// This matches all CJK ideographs.
        	var cjkRegEx = /[\u3400-\u4db5\u4e00-\u9fa5\uf900-\ufa2d]/;
        	
        	// This matches all characters that "break up" words.
        	var wordBreakRegEx = /\W/;
        	
        	var wordCount = 0;
        	var inWord = false;
        	var length = jsText.length;
        	for (var i = 0; i < length; i++) {
        		var curChar = jsText.charAt(i);
        		if (cjkRegEx.test(curChar)) {
        			// Character is a CJK ideograph.
        			// Count it as a word.
        			wordCount += inWord ? 2 : 1;
        			inWord = false;
        		} else if (wordBreakRegEx.test(curChar)) {
        			// Character is a "word-breaking" character.
        			// If a word was started, increment the word count.
        			if (inWord) {
        				wordCount += 1;
        			}
        			inWord = false;
        		} else {
        			// All other characters are "word" characters.
        			// Indicate that a word has begun.
        			inWord = true;
        		}
        	}
        	
        	// If the text ended while in a word, make sure to count it.
        	if (inWord) {
        		wordCount += 1;
        	}
        	
        	return wordCount;
        },

        elis2SetChiCount: function(){
        	elis2_count_chinese = true;
        },

        elis2CalculateWord: function(fullStr){
        	fullStr = fullStr+ " ";
        	var initial_whitespace_rExp = /^[^A-Za-z0-9]+/gi;
        	var left_trimmedStr = fullStr.replace(initial_whitespace_rExp, "");
        	var non_alphanumerics_rExp = rExp = /[^A-Za-z0-9]+/gi;
        	var cleanedStr = left_trimmedStr.replace(non_alphanumerics_rExp, " ");
        	var splitString = cleanedStr.split(" ");

        	return splitString.length -1;
        },

        elis2InitCheckText: function(element_id){
        	YUI().use('node', function(Y) {
        		
        		if(elis2_count_chinese==true)
        			var word = block_elis2.elis2CalculateChiWord(Y.one('#'+element_id).get('value'));
        		else
        			var word = block_elis2.elis2CalculateWord(Y.one('#'+element_id).get('value'));
        		block_elis2.elis2UpdateCounterBox(element_id,word);
        		block_elis2.elis2UpdateSubmitBtn();
        		
        		Y.all('#'+element_id).on('keyup', function (e) {

        			if(elis2_count_chinese==true)
        				var word = block_elis2.elis2CalculateChiWord(Y.one('#'+element_id).get('value'));
        			else
        				var word = block_elis2.elis2CalculateWord(Y.one('#'+element_id).get('value'));
        			block_elis2.elis2UpdateCounterBox(element_id,word);
        			block_elis2.elis2UpdateSubmitBtn();
        	    });
        	});
        },


        elis2UpdateSubmitBtn: function(){
        	
        	YUI().use('node', function(Y) {
        		var wc_cr	= Y.all('.wc_criteria');
        		var total	= 0;
        		var len		= 0;
        		
        		// can't break the each loop..
        		wc_cr.each(function (taskNode) {
        			if(taskNode.get('value')==0){
        				len--;
        			}else
        				len++;
        			total++;
        		});
        		if(len==total)
        			block_elis2.elis2EnableSubmit();
        		else
        			block_elis2.elis2DisableSubmit();
        								
        	});
        },
        elis2DisableSubmit: function(){
        	elis2_act_meet_criteria = false;
        },


        elis2EnableSubmit: function(){
        	elis2_act_meet_criteria = true;
        },

        elis2UpdateCounterBox: function(count_element_id,wc){
        	
        	YUI().use('node', function(Y) {
        		if(typeof(wc)=='undefined') wc = 0;
        		var min = parseInt(Y.one('#'+count_element_id+'_min_word').get('value'));
        		var max = parseInt(Y.one('#'+count_element_id+'_max_word').get('value'));
        	
        	    var of = Math.round(0.2*max);
        	    var mmx = max+of;
        	    var wcm = (wc>mmx)?mmx:wc;
        	    var out='';
        	    var mult=Math.round(200/mmx);
        	    var ww=2;
        	    
        	    var meet_criteria = 0;
            
        	    if(wc<min){
        	        out = "<img src='images/red.gif' height='18' width='"+ wcm*mult +"' />";
        	        out = out + "<img src='images/black.gif'  height='18' width='"+ ww + "' />";
        	        out = out + "<img src='images/red.gif' height='18' width='" + (((min-wcm)*mult)-ww) + "' />";
        	        out = out + "<img src='images/green.gif' height='18' width='"+ (max-min)*mult +"' />";
        	        out = out + "<img src='images/red.gif' height='18' width='"+ of*mult +"' />";
        	        out = out + "<img src='images/red.gif' height='18' width='"+ (220-((max+of)*mult)) +"' />";
        	        ok = 0;
        	        clr='red';
        	        
        	    }else if(wc<max){
        	        out = "<img src='images/red.gif' height='18' width="+ min*mult +" />"
        	        out = out + "<img src='images/green.gif' height='18' width="+ (wcm-min)*mult +" />"
        	        out = out + "<img src='images/black.gif'  height='18' width='"+ ww + "' />";
        	        var f = (wcm-min<0)?wcm-min:0;
        	        out = out + "<img src='images/green.gif' height='18' width="+ (((max-wcm-f)*mult)-ww) + " />"
        	        out = out + "<img src='images/red.gif' height='18' width="+ of*mult +" />"; 
        	        out = out + "<img src='images/red.gif' height='18' width="+ (220-(max+of)*mult) +" />"; 
        	        ok=1;
        	        clr='green';
        	        meet_criteria = 1;
        	    }else if(wc==max){
        	        out = "<img src='images/red.gif' height='18' width="+ min*mult +" />"
        	        out = out + "<img src='images/green.gif' height='18' width="+ (wcm-min)*mult +" />"
        	        out = out + "<img src='images/black.gif'  height='18' width='"+ ww + "' />";
        	        out = out + "<img src='images/red.gif' height='18' width="+ (of*mult-ww) +" />"; 
        	        out = out + "<img src='images/red.gif' height='18' width="+ (220-(max+of)*mult) +" />"; 
        	        ok=1;
        	        clr='green';
        	        meet_criteria = 1;
        	    }else if(wc>max){
        	        mm = (1*max)+of;
        	        out = "<img src='images/red.gif' height='18' width="+ min*mult +" />"
        	        out = out + "<img src='images/green.gif' height='18' width="+ (max-min)*mult +" />"
        	        out = out + "<img src='images/red.gif' height='18' width="+ (wcm-max)*mult +" />"
        	        out = out + "<img src='images/black.gif'  height='18' width='"+ ww + "' />";
        	        out = out + "<img src='images/red.gif' height='18' width="+ ((mm-wcm)*mult-ww) +" />";
        	        out = out + "<img src='images/red.gif' height='18' width="+ (220-(max+of)*mult) +" />";
        	        ok=2;
        	        clr='red';
        	        
        	    }
        	    
        	    Y.one('#'+count_element_id+'_wc_word').setContent(wc+(wc>1?' words':' word'));
        	    
        	    wc = '0'+wc;
        	    wc = wc.substr(-3);
            	
        		Y.one('#'+count_element_id+'_wc_bar').setContent(out);
        		// set criteria
        		Y.one('#wc_criteria_'+count_element_id).set('value', meet_criteria);
        	});
        },
        elis2InitTab : function(dummy,div_name) {
        	YUI().use('tabview', function(Y) {
        	    var tabview = new Y.TabView({srcNode:'#'+div_name});
        	    tabview.render();
        	    
        	});
        },
        elis2LoadReportData : function(dummy,type) {
        	YUI().use('io','node', function(Y) {
    			var period		= Y.one('#period').get('value');
    			var course_id	= Y.one('#course_id').get('value');
    			var data		= {course_id:course_id, period:period,type:type,task:'get_report_data'};
    			
    			
    	        Y.io('ajax.php', {
    	            method: 'GET',
    	            data: data,
    	            on: {
    	                success: function (id, result) {
    	                	YUI().use('json-parse', 'json-stringify', function (Y) {
    	                		var json = Y.JSON.parse(result.responseText);
    	                		YUI().use('node', 'button', function(Y) {
    	                			Y.one('#'+type+'_content').setContent(json.return_html);
    	                		});
    	                	});
    	                }
    	            }
    	        });
    			
    		});
        }
    };

}());







