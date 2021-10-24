import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PageCaptionComponent } from './page-caption.component';

describe('PageCaptionComponent', () => {
  let component: PageCaptionComponent;
  let fixture: ComponentFixture<PageCaptionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PageCaptionComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(PageCaptionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
