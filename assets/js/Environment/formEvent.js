import $ from 'jquery';

class FormEvent {
  constructor() {
    this.form = $("form[name='form']").first();
    this.path = Routing.generate('environment_deploy', { id: environmentId });

    this.form.on('submit', event => {
      event.preventDefault();
      $.post(this.path, $(event.target).serialize());
    });
  }

}

$(document).ready(function () {
  const formEvent = new FormEvent();
});
