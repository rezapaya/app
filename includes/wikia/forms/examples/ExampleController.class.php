<?
class ExampleController extends WikiaSpecialPageController {
	public function index() {
		// create form instance and pass it into view
		$this->form = new ExampleForm();

		$vals = [
			'contactFormSubject' => 'Example subject',
			'contactFormMessage' => 'Example message',
			'contactFormSendCopy' => 1,
			'contactFormSubmit' => wfMessage( 'submit' )->text(),
			'contactFormSessionId' => md5( 'random-session-key' ),
		];

		// validate form values (they can be taken from $_POST or $_GET or other source)
		if ($this->request->wasPosted() && $this->form->validate($vals)) {
			//save data && redirect
		}
		// else render form with error messages

		// set fields values that should be displayed in form
		$this->form->setFieldsValues($vals);
	}

	// TODO: implement forms in mustache
	// work in progress
	public function mustache() {
		$loginField = new stdClass();
		$loginField->inputField = true;
		$loginField->type = 'text';
		$loginField->name = 'login';
		$loginField->value = '';

		$passwordField = new stdClass();
		$passwordField->inputField = true;
		$passwordField->type = 'password';
		$passwordField->name = 'password';
		$passwordField->value = '';

		$message = new stdClass();
		$message->textarea = true;
		$message->name = 'message';
		$message->id = 'formMessage';
		$message->value = '';

		$question = new stdClass();
		$question->select = true;
		$question->name = 'question';
		$question->choices = [
			[ 'optionValue' => 1, 'option' => 'Yes' ],
			[ 'optionValue' => 0, 'option' => 'No' ]
		];
		$question->value = '';

		$this->form = new stdClass();

		$this->form->id = 'testId';
		$this->form->method = 'post';
		$this->form->action = 'test/action.php';

		$this->form->fields = [ $loginField, $passwordField, $message, $question ];
		$this->form->loginField = $loginField;
		$this->form->passwordField = $passwordField;
		$this->form->message = $message;
		$this->form->question = $question;

		$this->response->setTemplateEngine( WikiaResponse::TEMPLATE_ENGINE_MUSTACHE );
	}
}
