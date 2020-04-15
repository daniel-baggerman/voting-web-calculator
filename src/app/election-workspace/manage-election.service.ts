import { Injectable } from '@angular/core';
import { election } from '../shared/election.model';

@Injectable({
  providedIn: 'root'
})
export class ManageElectionService{
  private _election: election;

  constructor() { }

  get election(): election{
    return this._election;
  }

  set election(election: election){
    this._election = election;
  }
}