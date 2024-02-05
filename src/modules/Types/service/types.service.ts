import { BadRequestException, Injectable } from '@nestjs/common';
import { FindManyOptions, FindOptionsOrder, Repository } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';
import { TypesGetObjectsFilterDTO } from '../DTO/TypesGetObjectsFilterDTO';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class TypesService extends IHeliosService<TypeEntity, TypesGetObjectsFilterDTO> {
  constructor(@InjectRepository(TypeEntity) private readonly typesRepository: Repository<TypeEntity>) {
    super(typesRepository);
  }

  async updateObject(typeData: Partial<TypeEntity>) {
    if (!typeData.ID) {
      throw new BadRequestException('ID moet ingevuld zijn.');
    }

    const existingType = await this.typesRepository.findOne({ where: { ID: typeData.ID } });

    if (!existingType) {
      throw new BadRequestException('Type om te updaten niet gevonden.');
    }

    const updatedType = this.typesRepository.merge(existingType, typeData);
    return this.typesRepository.save(updatedType);
  }

  async restoreObject(id?: number) {
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');
    const existingType = await this.typesRepository.findOne({ where: { ID: id } });

    if (!existingType) {
      throw new BadRequestException('Type om te herstellen niet gevonden.');
    }

    existingType.VERWIJDERD = false;
    return this.typesRepository.save(existingType);
  }

  protected buildFindOptions(filter: TypesGetObjectsFilterDTO): FindManyOptions<TypeEntity> {
    const findOptions: FindManyOptions<TypeEntity> = {};
    const where: FindOptionsWhere<TypeEntity> = {};
    let order: FindOptionsOrder<TypeEntity> = {
      // todo: CLUBKIST bestaat niet in de entity, maar staat zo gedocumenteerd in de oude swagger file.
      // CLUBKIST: 'DESC',
      // VOLGORDE: 'ASC',
      // REGISTRATIE: 'ASC',
    };


    if (filter.ID) {
      where.ID = filter.ID;
    }

    if (filter.VERWIJDERD === undefined) {
      where.VERWIJDERD = false;
    } else {
      where.VERWIJDERD = filter.VERWIJDERD;
    }

    if (filter.LAATSTE_AANPASSING) {
      where.LAATSTE_AANPASSING = filter.LAATSTE_AANPASSING;
    }

    if (filter.GROEP) {
      where.GROEP = filter.GROEP;
    }

    if (filter.SORT) {
      order = this.bouwSorteringOp(filter.SORT);
    }

    if (filter.MAX) {
      findOptions.take = filter.MAX;
    }

    if (filter.START) {
      findOptions.skip = filter.START;
    }

    if (filter.VELDEN) {
      const select: Record<string, boolean> = {};
      // VELDEN is een comma separated string met de velden die je wilt selecteren.
      // TypeORM wil graag een object met de velden die je wilt selecteren, waarbij de waarde true is.
      // Bijvoorbeeld: { ID: true, OMSCHRIJVING: true }

      const velden = filter.VELDEN.split(',');
      velden.forEach((veld) => {
        select[veld.trim()] = true;
      });
      findOptions.select = select;
    }

    findOptions.where = where;
    findOptions.order = order;
    return findOptions;
  }

  /**
   * Zet de sortering om naar een FindOptionsOrder object
   * Input: SORT=CLUBKIST DESC, VOLGORDE, REGISTRATIE
   * Output: { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   * @param commaSeparatedString
   * @private
   */
  protected bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<TypeEntity> {
    const order: Record<string, string> = {};

    const sortFields = commaSeparatedString.split(',');

    sortFields.forEach((sortField) => {
      const parts = sortField.trim().split(' ');
      const field = parts[0];
      // Pak de de waarde van de sortering, als die er niet is, dan default naar ASC
      order[field] = parts.length > 1 ? parts[1] : 'ASC';
    });

    return order;
  }
}
