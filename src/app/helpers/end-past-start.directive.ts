import { Directive, Input } from '@angular/core';
import { NG_VALIDATORS, Validator, AbstractControl, ValidationErrors } from '@angular/forms';

@Directive({
  selector: '[EndPastStart]',
  providers: [{ provide: NG_VALIDATORS, useExisting: EndPastStartDirective, multi: true }]
})
export class EndPastStartDirective implements Validator {
  @Input('start_date') start_date: Date;

  validate(control: AbstractControl): ValidationErrors|null {
      if (this.start_date === null) {
        return null;
      } else {
        return control.value < this.start_date ? {start_after_end: true} : null;
      }
  }

}
