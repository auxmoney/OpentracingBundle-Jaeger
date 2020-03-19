# auxmoney OpentracingBundle - Jaeger

![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/auxmoney/OpentracingBundle-Jaeger)
![Travis (.org)](https://img.shields.io/travis/auxmoney/OpentracingBundle-Jaeger)
![Coveralls github](https://img.shields.io/coveralls/github/auxmoney/OpentracingBundle-Jaeger)
![Codacy Badge](https://api.codacy.com/project/badge/Grade/dd11fb9bdbe54affb1946a03af5f432a)
![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/auxmoney/OpentracingBundle-Jaeger)
![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/auxmoney/OpentracingBundle-Jaeger)
![GitHub](https://img.shields.io/github/license/auxmoney/OpentracingBundle-Jaeger)

This symfony bundle provides a tracer implementation for [Jaeger](https://www.jaegertracing.io/) for the [auxmoney OpentracingBundle](https://github.com/auxmoney/OpentracingBundle-core).

Please have a look at [the central documentation](https://github.com/auxmoney/OpentracingBundle-core) for installation and usage instructions.

## Configuration

You can optionally configure environment variables, however, the default configuration will sample every request.
If you cannot change environment variables in your project, you can alternatively overwrite the container parameters directly.

| environment variable | container parameter | type | default | description |
|---|---|---|---|---|
| AUXMONEY_OPENTRACING_SAMPLER_CLASS | auxmoney_opentracing.sampler.class | `string` | `Jaeger\Sampler\ConstSampler` | class of the using sampler, see [existing samplers](#existing-samplers) |
| AUXMONEY_OPENTRACING_SAMPLER_VALUE | auxmoney_opentracing.sampler.value | `string` | `'true'` | must be a JSON decodable string, for the configuration of the sampler |
 
### Existing Samplers

* constant sampler
    * Class: `Jaeger\Sampler\ConstSampler` 
    * possible values: `'true'` / `'false'`
    * Description: you activate or deactivate the tracing

* probabilistic sampler
    * Class: `Jaeger\Sampler\ProbabilisticSampler` 
    * possible values: Rate min `'0.00'` - max `'1.00'`
    * Description: you activate the tracing for the given rate
