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

function page_update_url()
{
	var root = $('#parent option[value=' + $('#parent').val() + ']').attr('label');
	if (root != '/')
	{
		root = root + '/';
	}
	$('.url-selector .parent').html(root);
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
});
