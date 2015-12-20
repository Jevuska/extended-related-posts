/*
 * @package EXTRP
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
( function( $ ) {
    $.fn.extrp = function() {
		var mainsc = $( ".main-sc" ).text();
			mainpc = $( ".main-pc" ).text();
			nullVal = $( "<em>",{ text : extrpL10n.null } ),
			msgErr = "msg-error",
			fileErr = "file-error",
			clsIpt = "new-input",
			clsBtnOK = "button button-secondary",
			idIpt = "btn-hlt",
			idBtnOK = "btn-ok-hlt",
			selfIdHl = "cb-hl",
			inputHl = $( "input#cb-highlight" ),
			pmstArray = ["posts", "maxchars", "subtitle", "titlerandom", "post__in", "post__not_in"],
			scarea = $( "#shortcode-generator-result" ),
			codearea = $( "#phpcode-generator-result" ),
			kbdrel = $( "input.relevanssi" ).parents( "tr" ).find( "td kbd" ),
			inputRelevanssi = $( "input.relevanssi" ).parents( "tr" ).find( "td kbd:last" ),
			kbdrelLst = ( 0 < inputRelevanssi.length ) ? inputRelevanssi : $( "input.widget-relevanssi" ).siblings( "label" ),
			extrp_nonce = heartbeatSettings.nonce;

		function sidebarPos() {
			if ( 0 == $( "#submitdiv" ).length )
				return;
			
			var sidebar = $( "#submitdiv" ),
				screenOption = $( "#screen-options-link-wrap" ),
				adminBar = $( "#wpadminbar" ),
				adminBarHeight = adminBar.height(),
				adminBarWidth = adminBar.width(),
				offset = $( "#submitdiv" ).offset(),
				sidebarTop = offset.top - adminBarHeight;
				
			scrollSidebar( sidebar, sidebarTop, adminBar, adminBarWidth, adminBarHeight );
			
			$(window).on( "resize", function() {
				var adminBarHeight = adminBar.height(),
					adminBarWidth = adminBar.width(),
					offset = $( "#submitdiv" ).offset(),
					sidebarTop = offset.top - adminBarHeight;
				scrollSidebar( sidebar, sidebarTop, adminBar, adminBarWidth, adminBarHeight );
			});
		}
		
		function scrollSidebar( sidebar, sidebarTop, adminBar, adminBarWidth, adminBarHeight ) {
			fixPos( sidebar, sidebarTop, adminBar, adminBarWidth, adminBarHeight );
			
			$( document ).scroll( function() {
					var adminBarWidth = adminBar.width();
					fixPos( sidebar, sidebarTop, adminBar, adminBarWidth, adminBarHeight );
				} );
		}
		
		function fixPos( sidebar, sidebarTop, adminBar, adminBarWidth, adminBarHeight ) {
			if ( window.pageYOffset >= sidebarTop && 850 < adminBarWidth )
				sidebar.css( { "position" : "fixed", "top" : adminBarHeight + "px", "width" : "278px" } );
			else
				sidebar.removeAttr( "style" );
		}
		
		function postbox_screen_option() {
			$( "input:not(:checked).hide-postbox-tog" ).each( function() {
					value = $( this ).val();
					$( "#" + value + ".postbox" ).hide();
			} )
		}
		
		function autoresize_mb_textarea() {
			$.each( $( ".meta-box-sortables textarea" ), function() {
				var offset = this.offsetHeight - this.clientHeight;
			 
				var resizeTextarea = function( el ) {
					$( el ).css( { "height" : "auto" } ).css( { "height" : el.scrollHeight + offset } );
				};
				$( this ).on( "keyup input", function() { resizeTextarea( this ) });
			})
		}

		function extrp_widget() {
			if ( "widgets" != pagenow )
				return false;
			
			$( ".extrp-widget-form" ).each( function() {
				var id = "#" + $( this ).parents( "div.widget" ).attr( "id" ),
					multi_number = $( id ).find( "input[name=multi_number]" ).val();
				hl_opt( id );
				extrp_ajax( id, multi_number );
			});
		}
		
		function extrp_ajax( id, multi_number ) {
			$( document ).ajaxSuccess( function( e, xhr, settings ) {
				if ( "widget-relevanssi" == this.activeElement.className )
					return false;
				
				if ( ( undefined != typeof settings.data.search && -1 != settings.data.search( "action=save-widget" ) && -1 != settings.data.search( "id_base=extrp_widget" ) ) ) {
					id = "#" + $( "#" + this.activeElement.id ).parents( "div.widget" ).attr( "id" );
					if ( id.indexOf( "__i__" ) && ( "" != multi_number ) ) {
						id = id.replace( "__i__", multi_number );
					}
				}
				if ( ( -1 != settings.data.search( "action=widgets-order" ) ) ) {
					id = "#" + $( this ).find( ".extrp-widget-form" ).parents( "div.widget" ).attr( "id" );
				}
				hl_opt( id );
			})
		}
		
        function hl_opt( id ) {
            var options = {
                defaultColor: "#333",
                change: function( event, ui ) {},
                clear: function() {},
                hide: true,
                palettes: true
            };
			
			$( id + " .highlight-select" ).change( function() {
				$( id + " .cp" ).hide();
				if ( $( id + " .select-col" ).is( ":selected" ) )  {
					$( id + " .cp-col" ).show( "fast", function() {
						$( id + " .col-field" ).wpColorPicker( options )
					} );
				} else if ( $( id + " .select-bgcol" ).is( ":selected" ) ) {
					$( id + " .cp-bgcol" ).show( "fast", function() {
						$( id + " .bgcol-field" ).wpColorPicker( options )
					});
				}
				else if ( $( id + " .select-css" ).is( ":selected" ) ) 
				{
					$( id + " .cp-css" ).show( "fast" );
				}
				else if ( $( id + " .select-class" ).is( ":selected" ) ) {
					$( id + " .cp-class" ).show( "fast" );
				}
			} ).change();
			$( id + " input.widget-relevanssi" ).off('click');
			$( id + " input.widget-relevanssi" ).on( "click", function( e ) {
				var t = '';
				if ( $( this ).is( ":checked" ) ) {
					e.stopImmediatePropagation();
					clearTimeout( t );
					t = setTimeout( function() {
						chk_relevanssi( id );
					}
					, 450 );
				}
			})
		}
        
        function crt_noimg_view() 
        {
			var frame,
				metaBox = $( "#thumbnail" ),
				addImgLink = metaBox.find( "#upload-custom-img" ),
				imgContainer = metaBox.find( ".custom-img-container" ),
				imgIdInput = metaBox.find( "#set-noimage" ),
				imgSize = metaBox.find( "select#image_size" ),	
				imgCrop = metaBox.find( "input#crop" ),
				imgShape = metaBox.find( "select#shape" );
			
			if ( $( "#set-noimage" ).length > 0 ) {
				if ( typeof wp !== "undefined" && wp.media && wp.media.editor ) {
					$( ".wrap" ).on( "click", ".set-noimage", function( e ) {
						e.preventDefault();
						e.stopPropagation();

						var sizeimg = imgSize.val(),
							shapeimg = imgShape.val();
						cropimg = $( imgCrop ).is( ":checked" ) ? 1 : 0;
							
						if ( $( this ).hasClass( "reset-noimage" ) ) {
							var srcimg = extrpSet.noimage;
							imgIdInput.val( srcimg );
							var attchID = $( "#attachment_id" ).val();
							return ajx_noimg_view( imgContainer, addImgLink, srcimg, attchID, sizeimg, shapeimg, cropimg );
						}
						
						if ( $( this ).hasClass( "change-size" ) ) {
							var srcimg = imgIdInput.val();
							var attchID = $( "#attachment_id" ).val();
							return ajx_noimg_view( imgContainer, addImgLink, srcimg, attchID, sizeimg, shapeimg, cropimg );
						}
						
						if ( frame ) {
						  frame.open();
						  return;
						}

						frame = wp.media( {
						  title: extrpL10n.mediatitle,
						  button: {
							text: extrpL10n.buttonmediatext
						  },
						  multiple: false
						} )

						frame.on( "select", function() {
							var attachment = frame.state().get( "selection" ).first().toJSON();
							var srcimg  = attachment.url
								attchID = attachment.id;
							
							ajx_noimg_view( imgContainer, addImgLink,srcimg, attchID, sizeimg, shapeimg, cropimg );
							imgIdInput.val( srcimg );
						})
						frame.open()
					})
				}
			}
		}
        
		function ajx_noimg_view( imgContainer, addImgLink, srcimg, attchID, sizeimg, shapeimg, cropimg ) {
			var data = {
				"action": "ajx_noimg_view_cb",
				"chk": pagenow,
				"nonce":extrp_nonce,
				"src":srcimg,
				"attach_id": attchID,
				"size":sizeimg,
				"shape":shapeimg,
				"crop":cropimg
			};
			
			$.post( ajaxurl, data, function( data, status, xhr ) {
				var token = data.result.tokenid;
				if ( 2 == token || 4 == token )
					imgContainer.html( $( "<p>",{"html":data.result.msg,"class":"attention"}) );
				if ( 3 == token )
					imgContainer.html( $( "<p>",{"text":wpAjax.noPerm,"class":"attention"}) );
				if ( 1 == token ) {
					imgContainer.removeAttr( "style" );
					if ( 500 < data.result.width || 500 < data.result.height ) {
					    imgContainer.css( {
							"max-width":"520px",
							"overflow":"scroll",
							"max-height":
							"300px"
						} );
					};
					imgContainer.html( $( "<p>", { "html": ( $( "<a>",{ "href": data.result.src, "class": "thickbox","title" : data.result.title, "html": $( "<img>" ).attr( {
						"src": data.result.thumbnail,
						"alt": data.result.title,
						"data-id": attchID, 
						"data-size": data.result.size,
						"data-title": data.result.title,
						"width": data.result.width,
						"height": data.result.height,
						"class": "extrp-shape-" + data.result.shape
					} ) } ) ) } ) );
				}
			}, "json" )
		}
        
        function select_preview() {
			$( "select#display,select#shape" ).on( "change", function() {
				$( this ).on( "click", function() {
						item = $( this ).attr( "id" );
						value = $( this ).val();
						$( ".extrp-"+ item).removeClass( "active current" );
						$( ".sample-" + item + "-" + value + ":not(.current)" ).addClass( "active current" );
						
						if (item == "shape" )
							$( "img#upload-custom-img" ).attr( "class","extrp-shape-" + value);
					} );
			});
			
            $( ".extrp-display,.extrp-shape" ).each( function() {
				$( this ).mouseover( function() {
					item = $( this ).attr( "data-img" );
					$( ".extrp-"+ item + ":not(.current)" ).removeClass( "active" );
					$( this ).addClass( "active" );
				})
				.mouseout( function() {
					$( ".extrp-"+ item + ":not(.current)" ).removeClass( "active" );
				});
					
				$( this ).on( "click", function( e ) {
					var value = $( this ).parent( "div" ).children( "span" ).text();
					e.preventDefault();
					$( ".extrp-" + item).removeClass( "active current" );
					$( this ).addClass( "active current" ).off( "mouseleave" );
					$( "select#" + item + " option[value='" + value + "']" ).prop( "selected", true);
						
					if ( "shape" == item )
						$( "img#upload-custom-img" ).attr( "class","extrp-shape-" + value);
						return false;
				})	
			} )
        }
		
        function generate_shortcode() {
            $( "#generate_code" ).on( "click", function( e ) {
                e.preventDefault();
                var sc = [],
					php = [];
                $( "table.table-extrp input:checked" ).each( function() {
					if ( $( this ).parents( "tr" ).find( "td:nth-child(4) kbd" ).hasClass( "active" ) ) {
						if ( "" != $( this ).val() ) {
							var value = $.trim( $( this ).val() );
							var thisVal = $( this ).parents( "tr" ).find( "i" ).text();
							if ( thisVal == "integer" || thisVal == "boolean" || thisVal == "array" ) {
								if ( -1 != value.indexOf( "_in" ) ) {
								values = value.replace( /="/g, "=array( " ).replace(/"/g, ')');
								} else if ( -1 != value.indexOf('_d') ) {
								values = value.replace( /="/g, "=array('" ).replace(/"/g, "')" ).replace( /,/g,"','" );
								} else {
								value = value.replace( /"/g, "" );
								values = value;
								}
							} else {
								values = value.replace( /"/g, "'" );
							}
							sc.push( value );
							php.push( values );
						}
					}
                });
				
                if ( 0 == sc.length ) {
					$( "table.table-extrp textarea" ).removeAttr( "style" );
                    scarea.val( mainsc );
					codearea.val( mainpc );
                } else {
                    sccode = $.trim( sc.join( " " )).replace( /\s\s+/g, ' ');
					phpcode = $.trim( php.join( ",\n'" )).replace( /\s\s+/gi, ' ').replace(/=/g, "' => " ).replace( /\n/g, "\n  " );

                    if ( 0 < sccode.length ) {
                        scarea.val( mainsc.substring( 0,17 ) + ' ' + sccode + mainsc.substring( 17 ));
						codearea.val( mainpc.substring( 6,34 ) + ",\n array(\n  '" + phpcode + "\n )\n);" ); 
						
						$( "table.table-extr textarea" ).removeAttr( "style" );
						$.each( $( "textarea[data-autoresize]" ), function() {
							var offset = this.offsetHeight - this.clientHeight;
			 
							var resizeTextarea = function( el ) {
								$( el ).css( "height", "auto" ).css( "height", el.scrollHeight + offset);
							};
							resizeTextarea( this );
						});
			
			
                    } else {
                       msg_error( $( "#msg-error" ), extrpL10n.notice4 );
                    }
                };
                return false;
            } );
        }
		
		function btn_reset() {
            $( "#reset_code" ).on( "click", function() {
                item_reset();
            })
        }
		
		function select_all() {
			var inputItem = $( "input:not(#cb-select-all-1), input:not(#cb-select-all-2)" );
            $( "#cb-select-all-1, #cb-select-all-2" ).on( "click", function() {
				if ( $( this ).is( ":checked" ) ) {
					inputItem.each( function() {
						each_sc( $( this ) );
					});
				} else {
					item_reset();
				}
            })
		}
		
        function select_par_shortcode() {
			$( "input:not(#cb-select-all-1), input:not(#cb-select-all-2)" ).each( function() {
                $( this ).on( "click", function() {
					each_sc( $( this ) );
                })
            })
        }
        
		function each_sc( self ) {
			var kbd = self.parents( "tr" ).find( "td:nth-child(4) kbd" );
			
			kbd.removeClass( "wp-ui-highlight active" );
			$( "." + clsIpt).remove();
			
			if ( self.is( ":checked" ) ) {
				if ( "cb-relevanssi" == self.attr( "id" )  ) {
					chk_relevanssi( "#extrp" );
				}
				kbd.each( function() {
					var el = $( this );
					var dft = el.closest( "td" ).siblings( "td:nth-child(3)" ).text().replace( "|no","" );
					if (el.text() == dft) {
						el.off( "click" ).css( {
							"color" : "#ccc",
							"background" : "#efefef"
						} );
					} else {
						shw_input( el, dft );
						chs_val( el, dft, kbd );
						hover_val( el );
					}
				})
			} else {
				unchk_param( self );
				kbd.each( function() {
					var el = $( this );
					el.off( "click mouseenter mouseleave" ).css( {
						"color" : "",
						"background" : ""
					} ).parents( "tr" ).find( "input" ).val( "" );
					$( "kbd[class='']" ).removeAttr( "class" );
					$( "kbd[style='']" ).removeAttr( "style" );
				})
			}
		}
		
		function item_reset() {
			$( "." + clsIpt ).remove();
			$( "input.posts" ).parents( "tr" ).find( "td:nth-child(4) kbd" ).text( 3 );
			$( "input.maxchars" ).parents( "tr" ).find( "td:nth-child(4) kbd" ).text( 256 );
			$( "input.subtitle" ).parents( "tr" ).find( "td:nth-child(4) kbd" ).text( extrpL10n.subtitletxt );
			$( "input.titlerandom" ).parents( "tr" ).find( "td:nth-child(4) kbd" ).text( extrpL10n.titlerandomtxt );
			$( "input.post__in" ).parents( "tr" ).find( "td:nth-child(4) kbd" ).text( "1,2,3" );
			$( "input.post__not_in" ).parents( "tr" ).find( "td:nth-child(4) kbd" ).text( "4,5,6" );
			$( "td div.hlt" ).html( $( "<kbd>",{ "text" : "#ff0000" }) );
			$( "table.table-extrp input[type=checkbox]" ).val( "" );
			$( "kbd" ).off( "click mouseenter mouseover" ).css( { "color": "", "background": "" } ).removeClass( "wp-ui-highlight active" );
			$( "kbd[class='']" ).removeAttr( "class" );
			$( "table.code-result textarea" ).removeAttr( "style" );
			$( "table.code-result textarea" ).val( "" );
			$( "table.table-extrp tr input" ).prop( "checked", false );
		}
		
        function unchk_param( self ) 
        {
			if (self.attr( "id" ) == selfIdHl) {
                if ( inputHlt.is( ":checked" ) )
                    inputHlt.prop( "checked", false ).val( "" );
				
				$( "td div.hlt kbd" ).off( "click mouseenter mouseleave" ).removeClass( "wp-ui-highlight active" ).removeAttr( "class" );
            }
        }
        
		function hover_val( el ) {
			el.mouseenter( function()
			{
				$( "kbd:not(.active)" ).removeClass( "wp-ui-highlight" );
				$( this ).addClass( "wp-ui-highlight" );
			}).mouseleave( function() {
				$( "kbd:not(.active)" ).removeClass( "wp-ui-highlight" );
				$( "td kbd[class='']" ).removeAttr( "class" );
			})
		}
		
        function shw_input_hl( dft, opt ) {
            var inputType = create_input_type( opt );
			
			switch( opt ) {
				case "col":
				case "bgcol":
					var options = {
						defaultColor: extrpSet.iecol,
						change: function( event, ui ) {
							var hexcolor = $( this ).wpColorPicker( "color" );
							
							$( "td div.hlt kbd" ).removeClass( "wp-ui-highlight active" );
							if ( "col" == opt ) {
								var bg = ( "#eaeaea" == hexcolor ) ? "#000000" : "#eaeaea";
								$( "td div.hlt kbd" ).text( hexcolor ).css( {
									"color" : hexcolor,
									"background" : bg
								} );
							};  
							
							if ( "bgcol" == opt ) {
								var txtcol = ( "#ffffff" == hexcolor ) ? "#333333" : "#ffffff";
								
							   $( "td div.hlt kbd" ).text( hexcolor ).css( {
									"color" : txtcol,
									"background" : hexcolor
								} );
							}
						},
						clear: function() {},
						hide: true,
						palettes: true
					};
                
					var input = $( "<div>", { "id": idIpt, "class": clsIpt, "html": $( "<p>", { "html" : inputType } ) } );
				
					$( "td div.hlt kbd" ).after( input );
					$( "#hltval" ).wpColorPicker( options );
				break;
				
				default :
					var input = $( "<div>", { "id" : idIpt, "class" : clsIpt,"html" : $( "<p>", { "html" : $( inputType ).add( $( "<button>", { "id" : idBtnOK, "class": clsBtnOK, "html" :extrpL10n.ok } ) ) } ) } );

					$( "td div.hlt kbd" ).after( input );
                
					if ( 0 < $( "#btn-hlt" ).length ) {
						$( "#btn-ok-hlt" ).click( function( e ) {
							e.preventDefault();
							var opts = $.trim( $( ".kbd-" + opt).val() );
							if ( 0 < opts.length ) {
								$( this ).parents( "div.hlt" ).children( "kbd" ).text( opts ).removeClass( "wp-ui-highlight active" );
								$( "#btn-hlt" ).remove();
							};
							return false;
						});
					}
				break;
			};
			chs_val( $( "td div.hlt kbd" ), dft );            
        }
        
        function shw_input( el, dft ) {
			var opt = el.parents( "tr" ).find( "input" ).attr( "class" );
			
			if ( -1 != pmstArray.indexOf( opt ) ) {
                var inputType = create_input_type( opt ),
					idBtn = "btn-" + opt,
					idBtnOK = "btn-ok-" + opt;
					
				var input = $( "<div>",{ "id": idBtn, "class": clsIpt, html: $( "<p>", { "html" : $( inputType ).add( $( "<button>",{ "id" :idBtnOK, "class" : clsBtnOK, "html" : extrpL10n.ok } ) ) } ) } );
				
				el.parents( "td" ).find( "kbd:last" ).after( input );
				
				if ( 0 < $( "#btn-" + opt).length ) {
					$( "#btn-ok-" + opt ).click( function( e ) {
					e.preventDefault();
					empty = $( "<em>", { text:extrpL10n.null } );
					var opts = $.trim( $( ".kbd-" + opt ).val() );
					if ( opts != dft ) {
						el.closest( "td" ).children( "kbd" ).css( {
							"color": "",
							"background": ""
						});
						
						if ( "posts" == opt || "maxchars" == opt ) {
							opts = ( $.isNumeric( opts ) && 0 < opts ) ? opts : 1;
							el.text( opts );
						}
						if ( "subtitle" == opt || "titlerandom" == opt ) {
							opts = ( "" == opts ) ? $( empty ) : opts;
							el.html( opts );
						}
						if ( "post__in" == opt|| "post__not_in" == opt ) {
							opts = opts.replace( /[^\d,]+/g, "" ).replace( /^[,\s]+|[,\s]+$/g, "" ).replace( /,[,\s]*,/g, "," );
							if ( "" == opts.length )
								return false;
							el.text(opts);
						}
						$( "#btn-" + opt ).remove();
					} else {
						msg_error( $( this ), extrpL10n.notice3 );
					}
					return false;
					})
				}
            }
        }
        
		function msg_error( that, notice ) {
			var msgError = $( "<p>", {
				"class": msgErr,
				"html": $( "<span>",{
					"class":fileErr,
					"text":notice
				}) 
			});
			
			$( "." + msgErr ).remove();
			that.after( msgError );
			$( "." + msgErr ).delay( 5000 ).fadeIn( "fast", function() {
				$( this ).fadeOut( "fast" ).remove();
			} )
		}
		
        function chs_val( el, dft, kbd ) {
			el.on( "click", function( e ) {
				e.stopImmediatePropagation();
				var self = $( this );
                var param = self.closest( "td" ).siblings( "td:nth-child(2)" ).text();
                var opt = self.text();
                if ( opt != dft ) {
                    $( "." + clsIpt ).remove();
                    if ( el.hasClass( "active" ) ) {
                        click_highlighted( el, dft );
                    } else {
						opt = ( opt == extrpL10n.null ) ? "" : opt;
						var valopt = param + '="' + opt + '"';
						var kbdHlVal = $( this ).closest( "div.hlt" ).parents( "td" ).find( "kbd.active" ).text();
						
						if ( $( this ).closest( "div.hlt" ).parents( "td" ).find( "kbd" ).hasClass( "active" ) ) {
							var valopt = param + '="' + kbdHlVal +'|'+ opt + '"';
						} else {
							if ( self.closest( "td" ).hasClass( "post_date" ) ) {
							} else {
								self.parents( "tr" ).find( "td:nth-child(4) kbd" ).removeClass( "wp-ui-highlight active" );
							}
						}
						self.addClass( "wp-ui-highlight active" );
						
						if ( "em" == opt|| "mark" == opt|| "no" == opt ) {
							var valopt = param + '="' + opt +'|'+ opt + '"';
						}
						
						if ( "col" == opt || "bgcol" == opt || "css" == opt || "class" == opt ) {
							var valopt = "";
						}
						
						if ( "show_date" == opt || "time_diff" == opt ) {
							var kbdDate = $( this ).siblings( "kbd" ).text();
							if ( $( this ).siblings( "kbd" ).hasClass( "active" ) ) {
								self.parents( "tr" ).find( "input" ).val( param + '="' + $( "td.post_date kbd" ).map( function() {
									return $( this ).text();
								}).get().join( "," ) + '"' );
							} else {
								var valopt = param + '="' + opt + '"';
								self.parents( "tr" ).find( "input" ).val( valopt );
							}
						} else {
							self.parents( "tr" ).find( "input" ).val( valopt );
						}
                        
                        self.css( {
                            "color" : "",
                            "background" : ""
                        } );
                        option_hl( dft, opt );
                    }
                }
                return false;
            }
            );
        }
        
        function click_highlighted( el, dft ) {
            el.removeClass( "wp-ui-highlight active" );

			if ( el.parents( "tr" ).find( "input" ).hasClass( "post_date" ) && el.siblings( "kbd" ).hasClass( "active" )) {
				var param = el.parents( "tr" ).find( "td:second" ).text();
				var kbdDatesSiblings = el.parents( "tr" ).find( "kbd.active" ).text();
				el.parents( "tr" ).find( "input" ).val( param + '="' + kbdDatesSiblings + '"' );
			} else {
				el.parents( "tr" ).find( "input" ).val( "" );
			}
			
			opt = el.parents( "td" ).find( "kbd.active" ).text();
	
            if ( ( "hlt" == el.closest( "div" ).attr( "class" ) ) && ( "col" == opt|| "bgcol" == opt || "css" == opt || "class" == opt ) )
                shw_input_hl( dft, opt );
			
            shw_input( el, dft );
        }
        
        function option_hl( dft, opt ) {
			switch( opt ) {
				case "col":
				case "bgcol":
					$( "td div.hlt" ).html( $( "<kbd>", { "text": extrpSet.iecol } ) );
					part_of_hl( dft, opt );
				break;
				
				case "css":
					$( "td div.hlt" ).html( $( "<kbd>", { "text": extrpSet.iecss } ) );
					part_of_hl( dft, opt );
				break;
				
				case "class":
					$( "td div.hlt" ).html( $( "<kbd>", { "text": extrpSet.ieclass } ) );
					part_of_hl( dft, opt );
				break;
				
				case "no":
				case "mark":
				case "em":
				case "strong":
					$( "." + clsIpt ).remove();
					$( "td div.hlt" ).html( $( "<kbd>", { "html" : opt, "class":"wp-ui-highlight active" } ) );
				break;
			}
        }
		
		function part_of_hl( dft, opt ) {
			$( "." + clsIpt ).remove();
			$( "td div.hlt kbd" ).removeClass( "wp-ui-highlight active" );
			$( "td div.hlt kbd" ).css( {
				"color" : "",
				"background" : ""
			});
			
			if ( inputHl.is( ":checked" ) )
				shw_input_hl( dft, opt );
        }
		
        function create_input_type( opt ) 
        {
			var input = "<input type='";
			
			switch( opt ) {
				case "subtitle" :
				case "titlerandom" :
				case "css" :
				case "class" :
				case "post__in" :
				case "post__not_in" :
					input += "text' ";
					input += "class='kbd-" + opt;
					input += "' value=''>";
					return input;
				break;
				case "col" :
				case "bgcol" :
					input += "hidden' ";
					input += "value='"+ extrpSet.iecol;
					input += "' id='hltval'>";
					return input;
				break;
				
				default :
					input += "number' ";
					input += "class='kbd-" + opt;
					input += "' min=1 value='1'>";
					return input;
				break;
			}
        }
        
		function chk_relevanssi( id ) {
			jqxhr = $.ajax( {
				type: "POST",
				url: ajaxurl,
				dataType: "json",
				data: {
					"action": "chk_relevanssi",
					"chk": pagenow,
					"nonce": extrp_nonce
				}
			})
			.done( function( data, status, xhr ) {
				data = data.result;
				if ( true == data )
					return;
				if ( ! data )
					msg_error( $( id ).find( kbdrelLst ), extrpL10n.notice5 );
				if ( 2 == data )
					msg_error( $( id ).find( kbdrelLst ), wpAjax.broken );
				if ( 3 == data )
					msg_error( $( '#' + id ).find( kbdrelLst ), wpAjax.noPerm );
				$( "input#cb-relevanssi,input.widget-relevanssi" ).prop( "checked", false );
				$( ".check-plugin" ).remove();
				kbdrel.removeClass( "wp-ui-highlight active" ).off( "click mouseenter mouseleave" ).css( {
					"color": "",
					"background": ""
				}).removeAttr( "class" );
			})
		}
		
		function disableDragMetaBox()
		{
			$( ".meta-box-sortables" ).sortable( {
				cancel: ".disable-drag"
			} );
			$( "#support, #features" ).addClass( "disable-drag" );
		}
		
        return this.each( function() {
			sidebarPos();
			postbox_screen_option();
            postboxes.add_postbox_toggles( pagenow );
			disableDragMetaBox();
			autoresize_mb_textarea();
            hl_opt( "#extrp" );
            crt_noimg_view();
            generate_shortcode();
            select_par_shortcode();
            select_preview();
			select_all();
            btn_reset();
			extrp_widget();
        })
    }
} ( jQuery ) );