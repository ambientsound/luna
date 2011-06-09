/*
 * LUNA content management system
 * Copyright (c) 2011, Kim Tore Jensen
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the author nor the names of its contributors may be
 * used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */


var sortable_inside = 0;

$(document).ready(function()
{
	$('#form_menus #mode').change(function()
	{
		if ($(this).val() == 'dynamic')
		{
			$('#fieldset-add_link_group').hide();
			$('#structure-div').show();
			$('#page_id-div').show();
		}
		else if ($(this).val() == 'static')
		{
			$('#fieldset-add_link_group').show();
			$('#structure-div').hide();
			$('#page_id-div').hide();
		}
	});

	$('#form_menus #add_link').click(function()
	{
		var par = $(this).parents('fieldset');
		var dest = $('#menu-items');
		var li = $('<li />');
		var ele = $('<input />');
		var div = $('<div />');
		var object = {
			page_id	: par.find('#add_page_link').val(),
			url	: par.find('#add_url_link').val(),
			title	: par.find('#add_title').val()
		};
		div.text(JSON.stringify(object));
		ele.attr('name', 'menuitem[]');
		ele.attr('type', 'hidden');
		ele.attr('value', JSON.stringify(object));
		li.append(ele);
		li.append(div);
		dest.append(li);
		par.find('input').val('');
	});

	$('#menu-items').sortable(
	{
		tolerance : 'pointer',

		stop : function(e, ui)
		{
			var id = [];
			$(this).children('li').each(function(index)
			{
				id.push($(this).children('img').attr('id'));
			});
			$('#pictures').attr('value', id);
		},
		receive : function(e, ui)
		{
			sortable_inside = 1;
			ui.placeholder.css('display', 'block');
		},
		over : function(e, ui)
		{
			sortable_inside = 1;
			ui.placeholder.css('display', 'block');
		},
		out : function(e, ui)
		{
			sortable_inside = 0;
			ui.placeholder.css('display', 'none');
		},
		beforeStop : function(e, ui)
		{
			if (sortable_inside == 0)
			{
				ui.item.remove();
			}
		}
	});

	$('#form_menus #add_page_link').change(function()
	{
		var addurl = $(this).parents('fieldset').find('#add_url_link-div');
		if ($(this).val() == '')
			addurl.show();
		else
			addurl.hide();
	});

	$('#form_menus #mode').trigger('change');
	$('#form_menus #add_page_link').trigger('change');
});
