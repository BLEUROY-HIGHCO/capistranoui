import $ from 'jquery';

class FormEvent {
  constructor() {
    this.form = $("form[name='form']").first();
    this.btnRollback = $(".btn-rollback");
    this.path = Routing.generate('environment_deploy', { id: environmentId });
    this.logger = $('#socket-content');

    this.form.on('submit', event => {
      event.preventDefault();
      $.post(this.path, $(event.target).serialize());
      this.logger.html('');
    });

    this.btnRollback.on('click', function (event) {
      event.preventDefault();
      $.get(Routing.generate('environment_rollback', { id: $(this).data('id'), token: $(this).data('token') }));
    });
  }

}

$(document).ready(function () {
  const formEvent = new FormEvent();
});
