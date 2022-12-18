var assert = require('assert');

describe('katalon', function() {

	it('should do something', function() {
		browser.url('http://localhost/21/login');
		browser.url('http://localhost/21/login');
		$('[name="username"]').setValue('admin@admin.com');
		$('[name="password"]').setValue('admin');
		$('//body').click();
		$('[name="username"]').setValue('presidente1');
		$('[name="password"]').click();
		$('[name="password"]').setValue('presidente1');
		$('//button[@type=\'submit\']').click();
		browser.url('http://localhost/21');
		$('=Asignar roles').click();
		$('(.//*[normalize-space(text()) and normalize-space(.)=\'Roles a aplicar\'])[1]/following::ul[1]').click();
		$('#select2-comittee-p0-container').click();
		$('//div[3]/span/span/span/ul/li/input').click();
		$('(.//*[normalize-space(text()) and normalize-space(.)=\'Aviso: Los usuarios seleccionados perderán sus roles antes de conseguir sus roles nuevos.\'])[1]/following::ul[1]').click();
		$('(.//*[normalize-space(text()) and normalize-space(.)=\'Aviso: Los usuarios seleccionados perderán sus roles antes de conseguir sus roles nuevos.\'])[1]/following::ul[1]').click();
		$('//button[@type=\'submit\']').click();
		$('=Gestionar alumnos').click();
		$('[name="username"]').setValue('admin@admin.com');
	});

});