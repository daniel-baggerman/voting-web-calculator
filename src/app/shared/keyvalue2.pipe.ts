import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'keyvalue2'
})
export class KeyValue2Pipe implements PipeTransform {

  transform(value: {}): string[] {
    if(!value){
      return [];
    }

    return Object.keys(value);
  }

}
