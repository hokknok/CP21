import { Image } from './image';


export interface FileData {
  status?: 'error' |  string;
  id?: number;
  name: string;
  src: string;
  ext: string;
  size: string;
  preview?: Image;
  desc?: string;
  errorText?: string;
}
