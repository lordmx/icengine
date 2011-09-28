/**
 * 
 * @desc Контроллер для администрирования разделов контента
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
var Controller_Admin_Content_Category = {
	
	/**
	 * @desc Состояние видимости подразделов
	 */
	subcatsVisibled: {},
	
	/**
	 * @desc Состояние видимости контента
	 */
	contentVisibled: {},
	
	/**
	 * @desc Загрузка списока подразделов
	 * @param category_id integer
	 * @param page integer
	 */
	getSubcategories: function (category_id, page)
	{
		function callback (result)
		{
			$('#category_' + category_id + '_children').html (result.html);
		}
		
		Controller.call (
			'Admin_Content_Category/getSubcategories',
			{
				category_id: category_id,
				page: page
			},
			callback
		);
	},
	
	/**
	 * @desc Загрузка списка контента для 
	 * @param category_id integer
	 * @param page integer
	 */
	getSubcontents: function (category_id, page)
	{
		function callback (result)
		{
			$('#category_' + category_id + '_content').html (result.html);
		}
		
		Controller.call (
			'Admin_Content_Category/getSubcontents',
			{
				category_id: category_id,
				page: page
			},
			callback
		);
	},
	
	/**
	 * @desc Разворачивает/сворачивает дочерние разделы
	 */
	toggleSubcategories: function (category_id)
	{
		if (this.subcatsVisibled [category_id])
		{
			$('#category_' + category_id + '_children').hide ();
			this.subcatsVisibled [category_id] = false;
			return;
		}
		
		if (typeof this.subcatsVisibled [category_id] == 'undefined')
		{
			this.getSubcategories (category_id);
		}
		
		$('#category_' + category_id + '_children').show ();
		this.subcatsVisibled [category_id] = true;
	},
	
	/**
	 * @desc Разворачивает/сворачивает контент
	 */
	toggleSubcontents: function (category_id)
	{
		if (this.contentVisibled [category_id])
		{
			$('#category_' + category_id + '_contents').hide ();
			this.contentVisibled [category_id] = false;
			return;
		}
		
		if (typeof this.contentVisibled [category_id] == 'undefined')
		{
			this.getSubcontents (category_id);
		}
		
		$('#category_' + category_id + '_contents').show ();
		this.contentVisibled [category_id] = true;
	}
	
};