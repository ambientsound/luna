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

var pic_chooser = null;

function update_picture_chooser(params)
{
	if (pic_chooser == null)
		return false;
	
	pic_chooser.children('.picture-placeholder').html('<img src="' + params.thumbnail + '" />');
	pic_chooser.children('input').attr('value', params.id);
	pic_chooser.children('a.remove').show();

	pic_chooser = null;

	mark_modified();
}

$(document).ready(function()
{
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

	$('.picture-element a.remove').click(function()
	{
		par = $(this).parents('.picture-element');
		par.find('input').attr('value', '');
		par.find('.picture-placeholder').html('');
		$(this).hide();

		mark_modified();
	});

	$('.picture-element').each(function()
	{
		if ($(this).children('.picture-placeholder').children().size() == 0)
			$(this).children('a.remove').hide();
	});
});
