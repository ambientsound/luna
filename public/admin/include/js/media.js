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

var uploadify_id = null;
var current_folder = 0;

function switch_folder(folder, recurse)
{
	current_folder = folder;
	var params =
	{
		'folder' : folder,
		'recurse' : recurse == true ? 1 : 0
	};

	$('.treeview-margin div').load('/admin/media', params);
}


$(document).ready(function()
{
	$('#treeview a').click(function()
	{
		var id = $(this).attr('rel');
		if (id == null)
			return false;

		if (id == current_folder)
			$('#treeview').jstree('rename');

		switch_folder(id, $('#recurse').is(':checked'));
	});

	$('#recurse').change(function()
	{
		switch_folder(current_folder, $(this).is(':checked'));
	});

	$('#treeview').bind('loaded.jstree', function()
	{
		$('#treeview').jstree('open_all');

	}).bind('rename.jstree', function(ev, data)
	{
		var params =
		{
			context : 'rename',
			id : data.rslt.obj.context.rel,
			name : data.rslt.new_name
		};
		$.post('/admin/media/folder', params);

	}).jstree(
	{
		core : {},
		ui : {
			initially_select : 'rootnode'
		},
		themeroller : {
			item : 'item',
			item_h : 'item-hover',
			item_leaf : 'ui-icon-folder-collapsed'
		},
		plugins : [ 'ui', 'html_data', 'crrm', 'themeroller' ]
	});


	/*
	*/

	$('#upload').uploadify(
	{
		script : '/admin/media/uploadify',
		uploader : '/admin/include/lib/uploadify/uploadify.swf',
		cancelImg : '/admin/include/lib/uploadify/cancel.png',
		auto : false,
		removeCompleted : false,
		fileDataName : 'upload',
		multi : true,

		onComplete : function(e, id, file, response, data)
		{
			uploadify_id = response;
			return true;
		},

		onAllComplete : function(e, data)
		{
			if (data.errors == 0)
			{
				if (data.filesUploaded == 1)
				{
					if (uploadify_id != null)
					{
						document.location = '/admin/media/read/id/' + uploadify_id;
						return;
					}
				}
				document.location = '/admin/media?sort=modified&order=desc';
			}
		}
	});

	$('#form_file').submit(function(e)
	{
		e.preventDefault();

		var o = {};
		var a = $('#form_file').serializeArray();

		$.each(a, function()
		{
			if (o[this.name] !== undefined)
			{
				if (!o[this.name].push)
				{
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			}
			else
			{
				o[this.name] = this.value || '';
			}
		});

		for (attrname in uploadify_scriptdata) { o[attrname] = uploadify_scriptdata[attrname]; }

		$('#upload').uploadifySettings('scriptData', o);
		$('#upload').uploadifyUpload();
	});
});
