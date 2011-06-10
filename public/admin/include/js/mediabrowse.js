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

function picture_insert_callback(data, st)
{
	if (st != 'success')
		return false;

	tinyMCEPopup.execCommand('mceReplaceContent', false, data);
	tinyMCEPopup.close();
}


$(document).ready(function()
{
	$('#form_mediabrowser').live('submit', function(e)
	{
		e.preventDefault();
		$.post($(this).attr('action'), $(this).serialize(), picture_insert_callback);
	});

	$('#folderselect img').live('click', function()
	{
		$(this).parents('ul').find('img').removeClass('active');
		$(this).addClass('active');
		$('#uploader').hide();
		$('#manager').show().load('/admin/util/mediabrowser', { id : $(this).attr('id') }, function()
		{
			$('#size').trigger('change');
			$('#link').trigger('change');
		});
	});

	$('#size').live('change', function()
	{
		if ($(this).val() == 'custom')
		{
			$('#customsize-div').show();
			$('#customsize').focus();
		}
		else
		{
			$('#customsize-div').hide();
		}
	});

	$('#link').live('change', function()
	{
		if ($(this).val() == 'custom')
		{
			$('#customlink-div').show();
			$('#customlink').focus();
		}
		else
		{
			$('#customlink-div').hide();
		}
	});

	$('#size').trigger('change');
	$('#link').trigger('change');
});
