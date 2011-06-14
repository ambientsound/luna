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

var page_slug_manually_changed = false;
var sortable_inside = 0;
var pic_chooser = null;
var picture_source_params =
{
	connectToSortable : '.picture-drop ul',
	cursor : 'move',
	opacity : 0.8,
	helper : 'clone',
	revert : 'invalid'
};

function page_update_url()
{
	var root = $('#parent option[value=' + $('#parent').val() + ']').attr('label');
	if (root != '/')
	{
		root = root + '/';
	}
	$('.url-selector .parent').html(root);
}

function update_picture_chooser(params)
{
	if (pic_chooser == null)
		return false;
	
	pic_chooser.children('.picture-placeholder').html('<img src="' + params.thumbnail + '" />');
	pic_chooser.children('input').attr('value', params.id);

	pic_chooser = null;
}

$(document).ready(function()
{
	$('#parent').change(function()
	{
		page_update_url();
	});

	$('#slug').keypress(function()
	{
		page_slug_manually_changed = true;
	});

	$('#title').change(function()
	{
		if (!$('#id').val() && (!page_slug_manually_changed || $('#slug').val() == ''))
		{
			$.get('/admin/util/slug', { source : $(this).val() }, function(data)
			{
				$('#slug').attr('value', data);
			});
		}
	});

	$('.picture-element button').click(function()
	{
		pic_chooser = $(this).parent();
		var id = $(this).siblings('input').val();
		var url = '/admin/media/browse?simple=1';
		if (id != null)
		{
			url += '&id=' + id;
		}

		window.open(
			url,
			'',
			'width=800,height=600'
		);
	});

	$('#nodetype').change(function()
	{
		$.getJSON('/admin/util/templates', { type : $(this).val() }, function(data)
		{
			var items = [];
			$.each(data, function(key, val)
			{
				items.push('<option value="' + key + '">' + val + '</option>');
			});
			$('#template').html(items.join(''));
		});
	});

	$('.picture-source #use_folder').change(function()
	{
		$(this).parents('.picture-source').siblings('.picture-drop').toggle();
	});

	$('#folder_id').change(function()
	{
		$.getJSON('/admin/util/files', { folder : $(this).val() }, function(data)
		{
			var list = $('#folder_id').parents('.picture-source').find('ul');
			list.children().remove();
			$.each(data, function(key, val)
			{
				var element = $('<img/>')
					.attr('id', val.id)
					.attr('src', val.thumbnail.small.pub)
					.attr('title', val.title)
					.attr('alt', val.alt);
				var container = $('<li/>');
				container.append(element);
				list.append(container);
			});
			list.children().draggable(picture_source_params);
		});
	});

	$('.picture-source ul li').draggable(picture_source_params);

	$('.picture-drop ul').sortable(
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
});
