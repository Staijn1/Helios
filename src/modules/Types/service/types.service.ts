import { Injectable } from '@nestjs/common';
import { FindManyOptions, Repository } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { createHash } from 'crypto';

@Injectable()
export class TypesService {
  constructor(@InjectRepository(TypeEntity) private readonly typesRepository: Repository<TypeEntity>) {}

  async getObject(id: number) {
    return this.typesRepository.findOne({ where: { ID: id } });
  }

  async getObjects(filter: FilterCriteria): Promise<TypeEntity[]> {
    const findOptions: FindManyOptions<TypeEntity> = {
      where: filter,
    };
    const dataset = await this.typesRepository.find(findOptions);
    const hash = createHash('md5').update(JSON.stringify(data)).digest('hex');

    return {
      totaal: dataset.length,
      laatsteAanpassing: new Date(),
      dataset: dataset,
      hash: hash,
    };
  }
}
