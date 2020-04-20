import { Injectable } from '@angular/core';
import { Subject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class MessagingService {
  private subject = new Subject<any>();
  light_dark_toggle = new Subject<any>();

  send_message(message:string){
    this.subject.next({ text: message });
  }

  clear_message(){
    this.subject.next();
  }

  get_message(): Observable<any>{
    return this.subject.asObservable();
  }
}
