(function() {
	tinymce.PluginManager.add('cbratingsystem', function( cbratingsystem_editor, url ) {
        var sh_tag = 'cbratingsystem';
		//helper functions 
		function getAttr(s, cbfc_n) {
			cbfc_n = new RegExp( ' ' + cbfc_n + '=\"([^\"]+)\"', 'g').exec(s);
			return cbfc_n ?  window.decodeURIComponent(cbfc_n[1]) : '';
		};

		function html( cls, data ,con) {
			//var placeholder = url + '/img/' + getAttr(data,'type') + '.jpg';
            //console.log(url);
			var placeholder = url + '/img/ratingshortcode.png';
			var data = window.encodeURIComponent( data );
			var content = window.encodeURIComponent( con );
			return '<img src="' + placeholder + '" class="mceItem ' + cls + '" ' + 'data-sh-attr="' + data + '" data-sh-content="'+ con+'" data-sh-shorttag="'+sh_tag+'" data-mce-resize="false" data-mce-placeholder="1" />';
		}

		function replaceShortcodes( content ) {
			return content.replace( /\[cbratingsystem([^\]]*)\]([^\]]*)\[\/cbratingsystem\]/g, function( all,attr,con) {
				return html( 'wp-cbratingsystem', attr , con);
			});

		}

		function restoreShortcodes( content ) {
			return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
				var data        = getAttr( image, 'data-sh-attr' );
				var con         = getAttr( image, 'data-sh-content' );
                var shorttag    = getAttr(image, 'data-sh-shorttag');

				if ( data && sh_tag ==  shorttag) {
					return '<p>[' + sh_tag + data + ']' + con + '[/'+sh_tag+']</p>';
				}
				return match;
			});
		}

		//add popup
		cbratingsystem_editor.addCommand('cbratingsystem_popup', function(ui, v) {
			//setup defaults
            
            var form_id = null;
			var showreview = 1;
			var theme_key = 'basic';


            if ( v.form_id != null ) {
                form_id = v.form_id;
            }

			if ( v.theme_key != null ) {
				theme_key = v.theme_key;
			}

			if ( v.showreview != null ) {
				showreview = v.showreview;
			}

			var forms 	= cbratingsystem_editor.getLang( 'cbratingsystem.forms' );
			forms 		= JSON.parse(forms);

			var themes 	= cbratingsystem_editor.getLang( 'cbratingsystem.themes' );
			themes 		= JSON.parse(themes);


			

           
			cbratingsystem_editor.windowManager.open( {

				title: cbratingsystem_editor.getLang( 'cbratingsystem.title' ),
				body: [
                    {
                        type:   'listbox',
                        name:   'form_id',
                        label:  cbratingsystem_editor.getLang( 'cbratingsystem.form_id_label' ),
                        value:  form_id,
                        'values': forms,
                        tooltip: cbratingsystem_editor.getLang( 'cbratingsystem.form_id_tooltip' )
                    },
					{
						type:   'listbox',
						name:   'showreview',
						label:  cbratingsystem_editor.getLang( 'cbratingsystem.showreview_label' ),
						value:  showreview,
						'values': [
							{text: cbratingsystem_editor.getLang( 'cbratingsystem.showreview_yes' ), value: '1'},
							{text: cbratingsystem_editor.getLang( 'cbratingsystem.showreview_no' ), value: '0'},
						],
						tooltip: cbratingsystem_editor.getLang( 'cbratingsystem.showreview_tooltip' )
					},
					{
						type:   'listbox',
						name:   'theme_key',
						label:  cbratingsystem_editor.getLang( 'cbratingsystem.theme_key_label' ),
						value:  theme_key,
						'values': themes,
						tooltip: cbratingsystem_editor.getLang( 'cbratingsystem.theme_key_tooltip' )
					}
					
				],
                onpostRender: function(e) {

                   /* var parentIdNum = parseInt( e.target._id.split( "_" )[1] );
                    var dateInputId = ( parentIdNum + 3 );

                    if ( jQuery( '#mceu_' + dateInputId ).length ) {
                        jQuery( '#mceu_' + dateInputId ).datepicker({ dateFormat: 'mm/dd/yy' });
                    }
*/
                    
                },

				onsubmit: function( e ) {
                    
                   
					var shortcode_str = '[' + sh_tag + ' form_id="'+e.data.form_id+'"';

					//if set date insert to shortcode
					if ( typeof e.data.showreview != null && e.data.showreview.length )
						shortcode_str += ' showreview="' + e.data.showreview + '"';

					//if set hour insert to shortcode
					if ( typeof e.data.theme_key != null && e.data.theme_key.length )
						shortcode_str += ' theme_key="' + e.data.theme_key + '"';

                    

					shortcode_str += '][/' + sh_tag + ']';
					//insert shortcode to tinymce
					cbratingsystem_editor.insertContent( shortcode_str);
				},

                onClose: function(w) {
                    //jQuery( '.colorpicker' ).hide();
                }
                //autoScroll: true,
                //width:500,
                //height:500,
                //'scroll-y':'scroll'



			});
        });

		//add button
		cbratingsystem_editor.addButton('cbratingsystem', {
			//icon: 'cbratingsystem',
			icon: 'dashicons-star-half',
			tooltip: cbratingsystem_editor.getLang( 'cbratingsystem.title' ),
			onclick: function() {
				cbratingsystem_editor.execCommand('cbratingsystem_popup','',{
					form_id:       '',
					showreview:    1,
					theme_key:     'basic',
				});
			}
		});

		//replace from shortcode to an image placeholder
		cbratingsystem_editor.on('BeforeSetcontent', function(event) {
			event.content = replaceShortcodes( event.content );
		});

		//replace from image placeholder to shortcode
		cbratingsystem_editor.on('GetContent', function(cbfc_event){
            cbfc_event.content = restoreShortcodes(cbfc_event.content);
		});

		//open popup on placeholder double click
		cbratingsystem_editor.on('DblClick',function(e) {
			var cls  = e.target.className.indexOf('wp-cbratingsystem');
			if ( e.target.nodeName == 'IMG' && e.target.className.indexOf('wp-cbratingsystem') > -1 ) {
				var title = e.target.attributes['data-sh-attr'].value;
				    title = window.decodeURIComponent(title);

				var content = e.target.attributes['data-sh-content'].value;
				cbratingsystem_editor.execCommand('cbratingsystem_popup','',{                    
					form_id: getAttr( title, 'form_id' ),
					showreview: getAttr( title, 'showreview' ),
					theme_key: getAttr( title, 'theme_key' ),

				});
			}
		});
	});
})();