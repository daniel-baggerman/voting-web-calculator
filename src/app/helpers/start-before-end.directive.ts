import { Directive, Input } from '@angular/core';
import { NG_VALIDATORS, Validator, AbstractControl, ValidationErrors } from '@angular/forms';

@Directive({
  selector: '[StartBeforeEnd]',
  providers: [{ provide: NG_VALIDATORS, useExisting: StartBeforeEndDirective, multi: true }]
})
export class StartBeforeEndDirective implements Validator {
  @Input('end_date') end_date: Date;

  validate(control: AbstractControl): ValidationErrors|null {
      if (this.end_date === null) {
        return null;
      } else {
        return control.value > this.end_date ? {start_after_end: true} : null;
      }
  }

}
