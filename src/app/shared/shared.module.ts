import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { AppRoutingModule } from '../app-routing.module';
import { RouterModule } from '@angular/router';
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';
import { MatDialogModule } from '@angular/material/dialog';
import { DialogModalComponent } from '../dialog-modal/dialog-modal.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        HttpClientModule,
        AppRoutingModule,
        RouterModule,
        MatDialogModule,
        BrowserAnimationsModule
    ],
    declarations: [
        HeaderComponent, 
        FooterComponent,
        DialogModalComponent
    ],
    exports: [
        CommonModule,
        FormsModule,
        HttpClientModule,
        RouterModule,
        BrowserAnimationsModule,
        HeaderComponent,
        FooterComponent
    ],
    entryComponents: [DialogModalComponent]
})
export class SharedModule { }