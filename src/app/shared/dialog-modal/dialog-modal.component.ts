import { Component, OnInit, Inject } from '@angular/core';

import {MatDialogRef, MAT_DIALOG_DATA} from  '@angular/material/dialog';

@Component({
  selector: 'app-dialog-modal',
  templateUrl: './dialog-modal.component.html',
  styleUrls: ['./dialog-modal.component.css']
})
export class DialogModalComponent implements OnInit {
  message: string;

  constructor(private  dialogRef:  MatDialogRef<DialogModalComponent>, 
              @Inject(MAT_DIALOG_DATA) public  data:  any) { }

  ngOnInit(): void {
    this.message = this.data.message;
  }

  close_me(value: number): void {
    this.dialogRef.close(value);
  }

}
