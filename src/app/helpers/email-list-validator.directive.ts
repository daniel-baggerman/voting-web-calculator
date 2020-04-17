import { Directive, Input } from '@angular/core';
import { Validator, NG_VALIDATORS, AbstractControl, ValidationErrors } from '@angular/forms';

@Directive({
  selector: '[EmailListValidator]',
  providers: [{ provide: NG_VALIDATORS, useExisting: EmailListValidatorDirective, multi: true }]
})
export class EmailListValidatorDirective implements Validator{
  @Input('email_list') email_list: string;

  validate(control: AbstractControl): ValidationErrors|null {
    // Uses regex to check each item in an comma separated list for valid email formatting 
    if(control.value == null || control.value == ""){
      return null;
    }
    let regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
        emails = control.value.replace(/\s/g,'').split(','), // expects comma separated list of emails
        valid = true;
    for (var i = 0; i<emails.length; i++){
      if( emails[i] == "" || !regex.test(emails[i])){
        valid = false;
      }
    }
    return valid ? null : {'invalid_email_list': true};
  }
}
