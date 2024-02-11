/**
 * In this file we call the PHP API and the new NestJS API, with the same endpoint and parameters.
 * Then, to validate if the NestJS API is working correctly, we compare the response of both APIs.
 * If the responses are the same, the test passes.
 *
 * Both APIs must be running for this test to work.
 */

import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication, Logger } from '@nestjs/common';
import { AppModule } from '../src/app.module';
import axios, { AxiosResponse } from 'axios';


const logger = new Logger('API Response Comparison (e2e)');

class EndpointGroup {
  constructor(public name: string, public endpoints: Endpoint[]) {
  }
}


class Endpoint {
  constructor(public name: string, public method: string, public path: string, public queryParams?: Record<string, unknown>) {
  }
}

class RequestBuilder {

  async makeRequest(baseurl: string, endpoint: Endpoint): Promise<AxiosResponse> {
    let request: Promise<AxiosResponse>;

    const auth = {
      username: 'HermanE', // replace with your username
      password: 'Test1234', // replace with your password
    };
    const url = baseurl + endpoint.path;

    switch (endpoint.method) {
      case 'GET':
        request = axios.get(url, {
          params: endpoint.queryParams,
          auth: auth,
          timeout: 5000,
        });
        break;
      // Add other HTTP methods as needed
      default:
        throw new Error(`Unsupported method: ${endpoint.method}`);
    }

    try {
      logger.log(`Making a request to "${url}". Query parameters:`, endpoint.queryParams);
      await request;
    } catch (e) {
      logger.error(`An error occurred while making a request to "${url}"`, e);
      throw e;
    }

    return request;
  }
}

describe('API Response Comparison (e2e)', () => {
  let app: INestApplication;
  let requestBuilder: RequestBuilder;

  const NESTJS_API_URL = 'http://localhost:3333';
  const PHP_API_URL = 'http://localhost:8081';

  const endpoints = [
    new EndpointGroup('TypesGroepen', [
      new Endpoint("ID", 'GET', '/TypesGroepen/GetObjects', { ID: 1 }),
      new Endpoint("Max", 'GET', '/TypesGroepen/GetObjects', { MAX: 2 }),
      new Endpoint("Sort ID ASC", 'GET', '/TypesGroepen/GetObjects', { SORT: 'ID' }),
      new Endpoint("Sort ID DESC", 'GET', '/TypesGroepen/GetObjects', { SORT: 'ID DESC' }),
      new Endpoint('GetObjects', 'GET', '/TypesGroepen/GetObjects'),
      new Endpoint("Velden", 'GET', '/TypesGroepen/GetObjects', { MAX: 2, VELDEN:"ID, OMSCHRIJVING" }),
    ]),
    new EndpointGroup('Types', [
      new Endpoint("ID", 'GET', '/Types/GetObjects', { ID: 601 }),
      new Endpoint("Max", 'GET', '/Types/GetObjects', { MAX: 2 }),
      new Endpoint("Sort ID ASC", 'GET', '/Types/GetObjects', { SORT: 'ID' }),
      new Endpoint("Sort ID DESC", 'GET', '/Types/GetObjects', { SORT: 'ID DESC' }),
      new Endpoint('GetObjects', 'GET', '/Types/GetObjects'),
      new Endpoint("Groep = 1", 'GET', '/Types/GetObjects', { GROEP: 1 }),
      new Endpoint("Velden", 'GET', '/Types/GetObjects', { MAX: 2, VELDEN:"ID, OMSCHRIJVING" }),
    ]),
    new EndpointGroup('Leden', [
      new Endpoint('ID', 'GET', '/Leden/GetObjects', { ID: 10858 }),
      new Endpoint('Max', 'GET', '/Leden/GetObjects', { MAX: 2 }),
      new Endpoint('Jeugdleden / Ereleden', 'GET', '/Leden/GetObjects', { TYPES: '601,603' }),
      new Endpoint('DDWV', 'GET', '/Leden/GetObjects', { TYPES: '625' }),
      new Endpoint('Lieristen', 'GET', '/Leden/GetObjects', { LIERISTEN: 1 }),
      new Endpoint('Startleiders', 'GET', '/Leden/GetObjects', { STARTLEIDERS: 1 }),
      new Endpoint('Instructeurs', 'GET', '/Leden/GetObjects', { INSTRUCTEURS: 1 }),
      new Endpoint('Instructeurs en Lieristen', 'GET', '/Leden/GetObjects', { INSTRUCTEURS: 1, LIERISTEN: 1 }),
      new Endpoint('DDWV crew', 'GET', '/Leden/GetObjects', { DDWV_CREW: 1 }),
      new Endpoint('Sort ID ASC', 'GET', '/Leden/GetObjects', { SORT: 'ID' }),
      new Endpoint('Sort ID DESC', 'GET', '/Leden/GetObjects', { SORT: 'ID DESC' }),
      new Endpoint('GetObjects', 'GET', '/Leden/GetObjects'),
      new Endpoint('Velden', 'GET', '/Leden/GetObjects', { MAX: 2, VELDEN: 'ID, NAAM' }),
    ]),
  ];

  beforeAll(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();
    app = moduleFixture.createNestApplication();
    await app.init();
  });

  beforeEach(async () => {
    requestBuilder = new RequestBuilder();
  });

  for (const endpointGroup of endpoints) {
    describe(`${endpointGroup.name}`, () => {
      for (const endpoint of endpointGroup.endpoints) {
        it(`${endpoint.name} - Compare PHP API and NestJS API response `, async () => {
          const nestjsResponse = await requestBuilder.makeRequest(NESTJS_API_URL, endpoint);
          const phpResponse = await requestBuilder.makeRequest(PHP_API_URL, endpoint);


          expect(nestjsResponse.status).toEqual(phpResponse.status);
          logger.log('Comparison of status codes completed: success');
          // Expect a status code to be 200, 201 or 204
          expect([200, 201, 204]).toContain(nestjsResponse.status);
          logger.log('NestJS response has 2xx status code: success');

          logger.log('Comparing response bodies...');
          expect(nestjsResponse.data).toEqual(phpResponse.data);
          logger.log('Comparison of response bodies completed: success');
        }, 20000);
      }
    });
  }
});
