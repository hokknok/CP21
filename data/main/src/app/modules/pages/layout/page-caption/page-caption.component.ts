import { Component, Input, OnInit } from '@angular/core';

@Component({
  selector: 'app-page-caption',
  templateUrl: './page-caption.component.html',
  styleUrls: ['./page-caption.component.scss']
})
export class PageCaptionComponent implements OnInit {
  @Input() public name: string;
  @Input() public desc: string;


  constructor() { }


  public ngOnInit(): void {
  }

}
