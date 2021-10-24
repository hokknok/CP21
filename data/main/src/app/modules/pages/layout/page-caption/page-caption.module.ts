import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PageCaptionComponent } from './page-caption.component';



@NgModule({
    declarations: [PageCaptionComponent],
    exports: [
        PageCaptionComponent
    ],
    imports: [
        CommonModule
    ]
})
export class PageCaptionModule { }
