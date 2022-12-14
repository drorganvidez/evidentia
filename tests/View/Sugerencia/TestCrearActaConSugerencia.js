var assert = require('assert');

describe('Test Crear Acta con Sugerencia', function() {

	it('should do something', function() {
		browser.url('http://localhost/21/login');
		$('[name="username"]').click();
		$('[name="username"]').setValue('secretario1');


	// WARNING: unsupported command . Object= {"command":"","target":"name=password","value":""}

		$('[name="password"]').setValue('secretario1');
		$('//button[@type=\'submit\']').click();
		$('(.//*[normalize-space(text()) and normalize-space(.)=\'REUNIONES Y BONOS\'])[1]/following::p[1]').click();
		$('=Crear convocatoria').click();
		$('[name="title"]').click();
		$('[name="title"]').setValue('Convocatoria de Prueba');
		$('#date').click();
		$('#date').setValue('2022-12-30');
		$('#time').click();
		$('#time').setValue('12:03');
		$('#time').setValue('12:30');
		$('[name="place"]').click();
		$('[name="place"]').setValue('ETSII');
		$('//form[@id=\'request_form\']/div[4]/div').click();
		$('#button_add_point').click();
		$('//button[@type=\'submit\']').click();
		$('=Mis actas').click();
		$('=Crear acta').click();
		$('//div[@id=\'step_1\']/form/div/div/div/button/div/div/div').click();
		$('//div[@id=\'step_1\']/form/div/div/div/div/div/ul/li[2]/a/span').click();
		$('#meeting_request').selectByVisibleText('Convocatoria de Prueba');
		$('//button[@type=\'submit\']').click();
		$('//button[@type=\'submit\']').doubleClick();
		$('//button[@type=\'submit\']').click();
		$('[name="hours"]').click();
		$('[name="hours"]').setValue('1');
		$('[name="minutes"]').click();
		$('[name="minutes"]').setValue('20');


	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Bennett, Stephan"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Chadwick, Diogo"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Clayton, Karl"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Cordova, Kathryn"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Doe, John"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Hart, Clara"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Hendricks, Margaret"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Liu, Jamie-Leigh"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Rowley, Diana"}



	// WARNING: unsupported command removeSelection. Object= {"command":"removeSelection","target":"id=bootstrap-duallistbox-nonselected-list_users[]","value":"label=Woolley, Samara"}

		$('//div[@id=\'point_1\']/div[2]/div/div/div/div/div/input').click();
		$('//div[@id=\'point_1\']/div[2]/div/div/div/div/div/input').setValue('Reunión de prueba');
		$('//div[@id=\'point_1\']/div[2]/div/div/div/div[2]/div/textarea').setValue('descripción de prueba');
		$('//div[@id=\'point_1\']/div[2]/div/div/div/div[2]/div[2]/textarea').setValue('sugerencia de prueba');
		$('//div[@id=\'point_1\']/div[2]/div/div[2]/div/div/div').click();
		$('//div[@id=\'point_1\']/div[2]/div/div[2]/div/div/div/input').click();
		$('//div[@id=\'point_1\']/div[2]/div/div[2]/div/div/div/input').setValue('90');
		$('//button[@type=\'submit\']').click();
		$('//table[@id=\'dataset\']/tbody/tr/td[6]/a[2]/i').click();
		$('[name="minutes"]').click();
		$('[name="minutes"]').setValue('30');
		$('//div[@id=\'point_1\']/div[2]/div/div/div/div[2]/div[2]/textarea').click();
		$('//div[@id=\'point_1\']/div[2]/div/div/div/div[2]/div[2]/textarea').setValue('sugerencia de prueba cambiada');
		$('//button[@type=\'submit\']').click();
		$('//table[@id=\'dataset\']/tbody/tr/td[6]/a/i').click();
		$('=Salir').click();
	});

});